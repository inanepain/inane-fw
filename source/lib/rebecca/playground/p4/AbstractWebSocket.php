<?php
/*
 * This is free and unencumbered software released into the public domain.
 *
 * Anyone is free to copy, modify, publish, use, compile, sell, or
 * distribute this software, either in source code form or as a compiled
 * binary, for any purpose, commercial or non-commercial, and by any
 * means.
 *
 * In jurisdictions that recognize copyright laws, the author or authors
 * of this software dedicate any and all copyright interest in the
 * software to the public domain. We make this dedication for the benefit
 * of the public at large and to the detriment of our heirs and
 * successors. We intend this dedication to be an overt act of
 * relinquishment in perpetuity of all present and future rights to this
 * software under copyright law.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * For more information, please refer to <http://unlicense.org/>
 */

/**
 * Inane: Rebecca
 *
 * Inane WebSocket Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\rebecca
 * @category websocket
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace p4;

use Inane\Config\Config;
use Inane\Stdlib\{
    Array\OptionsInterface,
    Json,
    Options};
use OpenSwoole\Http\Request;
use OpenSwoole\WebSocket\{
    Frame,
    Server};
use WeakReference;

use function time;

use const PHP_EOL;

/**
 * AbstractWebSocket
 *
 * Base class that encapsulates the common OpenSwoole WebSocket server lifecycle and
 * event dispatching. It owns the server instance, wires event callbacks, and
 * routes events to per-connection `MessageHandler` instances via `EventSocket`.
 */
abstract class AbstractWebSocket {
    //#region Class Constants
    public const int ADDED   = 1 << 0;
    public const int UPDATED = 1 << 1;
    //#endregion Class Constants
    //#region Properties
    /**
     * Weak Instance Reference
     *
     * @var WeakReference
     */
    private static WeakReference $instance;
    /**
     * Set flags
     *
     * @var mixed
     */
    protected int $flags;
    // const FLAG_REGISTERED = 1 << 2;
    protected int $refreshed = 0;
    protected Server $server;
    protected OptionsInterface $eventHandlers;
    //#endregion Properties

    /**
     * @param OptionsInterface|Config $config
     */
    private function __construct(
        protected OptionsInterface $config,
    ) {
        $this->bootstrap();
    }

    /**
     * Initialise and retrieve the singleton-like instance (weakly referenced).
     *
     * If no configuration is provided on the first call, a default empty Options
     * instance will be used. Subsequent calls return the existing instance.
     *
     * @param null|OptionsInterface $config Server and runtime configuration
     *
     * @return static The initialized WebSocket server instance
     */
    public static function init(?OptionsInterface $config = null): static {
        if (!isset(static::$instance)) {
            $config ??= new Options();
            static::$instance = WeakReference::create(new static($config));
        }

        /**
         * @var AbstractWebSocket static::$instance->get()
         */
        return static::$instance->get();
    }

    /**
     * Get the age in seconds since last refresh.
     *
     * @return int Seconds elapsed since $refreshed
     */
    private function age(): int {
        return time() - $this->refreshed;
    }

    /**
     * Refresh the timestamp if a configured age threshold has passed.
     *
     * @return bool True if refreshed, false otherwise
     */
    private function update(): bool {
        if ($this->age() > $this->config->age) {
            $this->refreshed = time();

            return true;
        }

        return false;
    }

    /**
     * Bootstrap internal state and instantiate the WebSocket server.
     *
     * Initializes the event handlers container and constructs the
     * OpenSwoole WebSocket Server from configuration, then wires
     * event callbacks via setupServer().
     *
     * @return void
     */
    protected function bootstrap(): void {
        $this->eventHandlers = new Options();
        $this->server = new Server(...$this->config->server->toArray());
        $this->setupServer();
    }

    /**
     * Dispatch an EventSocket to its connection-specific handler or handle
     * built-in commands for message events.
     *
     * For the first event on a connection id, a new MessageHandler instance is
     * created and stored. On 'close', the handler is removed. For 'message', a
     * small set of built-in commands (e.g. 'shutdown') may be processed here
     * and a response sent directly.
     *
     * @param EventSocket $event The event to dispatch/handle
     *
     * @return mixed True/false for built-in message handling; otherwise the
     *               result returned by the handler method (often void)
     */
    protected function trigger(EventSocket $event) {
        if (!$this->eventHandlers->has($event->id)) $this->eventHandlers[$event->id] = new MessageHandler($event->id);

        $handler = $this->eventHandlers[$event->id];
        if ($event->name === 'close') $this->eventHandlers->offsetUnset($event->id); elseif ($event->name === 'message') {
            $response = [
                'command' => $event->command,
                'result'  => null,
                'type'    => false,
            ];

            if ($event->command === 'shutdown') {
                $response['result'] = 'Server Shutdown!';
                $response['type'] = 'system';

                echo "Command: {$event->command} => server shutting down...";
                $event->server->shutdown();
            } elseif ($event->getCommand() === 'reload') {
                $response['result'] = 'Server Reloaded.';
                $response['type'] = 'system';

                echo "Command: {$event->command} => server reloading...";
                $event->server->reload();
            }

            if ($response['result'] !== null) {
                $event->server->push($event->id, Json::encode($response));

                return true;
            }

            return false;
        }

        return ($handler)->{$event->name}($event);
    }

    /**
     * Register OpenSwoole event callbacks and bridge them to trigger().
     *
     * Hooks the following events:
     * - start: logs server address
     * - open: creates/initializes per-connection handler
     * - message: dispatches to built-ins or per-connection handler
     * - close: removes per-connection handler
     *
     * @return void
     */
    protected function setupServer(): void {
        $config = $this->config;

        $this->server->on('start', $this->onStart(...));
        $this->server->on('open', $this->onOpen(...));
        $this->server->on('close', $this->onClose(...));
        $this->server->on('message', $this->onMessage(...));
    }

    #region Event Handlers

    /**
     * Handles the server start process and prints server startup information,
     * including the host and port where the WebSocket server is running.
     *
     * @param Server $server The WebSocket server instance that has been started
     *
     * @return void
     */
    protected function onStart(Server $server): void {
        echo "OpenSwoole WebSocket Server is started at wss://{$this->config->server->host}:" . (string)$this->config->server->port . PHP_EOL;
    }

    /**
     * Hook for connection open events (currently logs to stdout).
     *
     * @param Server  $server  OpenSwoole server instance
     * @param Request $request Request carrying connection info (e.g. fd)
     *
     * @return void
     */
    protected function onOpen(Server $server, Request $request): void {
        $this->trigger(new EventSocket(['id' => $request->fd, 'name' => 'open', 'request' => $request, 'server' => $server]));
    }

    /**
     * Hook for connection close events (currently logs to stdout).
     *
     * @param Server $server OpenSwoole server instance
     * @param int    $fd     Connection id/file descriptor
     *
     * @return void
     */
    protected function onClose(Server $server, int $fd): void {
        $this->trigger(new EventSocket(['id' => $fd, 'name' => 'close', 'server' => $server]));
    }

    /**
     * Handles incoming websocket messages from a client, processes commands, and sends appropriate responses.
     *
     * The method parses the received data, executes specified commands, and sends responses to the client.
     * Built-in commands such as 'help', 'hello', and 'reload' are handled directly within this method.
     * If custom event triggers are defined, they are executed before handling the default commands.
     *
     * @param Server $server The websocket server instance handling the connection
     * @param Frame  $frame  The incoming websocket frame containing the message data and connection details
     *
     * @return void
     */
    protected function onMessage(Server $server, Frame $frame): void {
        echo "received message [{$frame->fd}]: {$frame->data}" . PHP_EOL;

        $data = new Options($frame->data);
        $data->complete(['command' => 'help']);
        $command = $data->command;
        $type = false;

        if ($this->trigger(EventSocket::createEvent($server, $frame->fd, 'message', $command, null, $data))) return;

        $package = new Options([
            'command' => $command,
            'result'  => null,
            'type'    => $type,
        ]);

        switch ($command) {
            case 'help':
                $custom = [
                    'result' => 'Commands: hello, info, bye, reload, shutdown',
                    'type'   => 'help',
                ];
                break;
            case 'hello':
                $custom = ['result' => 'Hello World!'];
                break;
            case 'info':
                $custom = ['result' => 'Worldly Data!'];
                break;
            case 'bye':
                $custom = [
                    'result' => 'Goodbye Cruel World!',
                    'type'   => 'system',
                ];
                break;
            default:
                $custom = [
                    'command' => 'UNKNOWN',
                    'result'  => 'The World!',
                ];
        }

        $package->merge($custom);

        $server->push($frame->fd, $package->toJSON());
    }
    #endregion Event Handlers

    /**
     * Is flag set
     *
     * @param int      $flag    the flag to test
     * @param null|int $options optional value to test instead of $flags property
     *
     * @return bool true if the flag is set
     */
    protected function isFlagSet(int $flag, ?int $options = null): bool {
        return ((($options ?? $this->flags) & $flag) == $flag);
    }

    /**
     * Set/unset a bitwise flag on this instance.
     *
     * @param int  $flag  bit mask to set/unset
     * @param bool $value true to set, false to unset
     *
     * @return self
     */
    protected function setFlag(int $flag, bool $value = true): self {
        if ($value) $this->flags |= $flag; else $this->flags &= ~$flag;

        return $this;
    }

    /**
     * Start the WebSocket server main loop.
     *
     * Blocks the current process until the server is stopped.
     *
     * @return void
     */
    public function run(): void {
        $this->server->start();
    }
}
