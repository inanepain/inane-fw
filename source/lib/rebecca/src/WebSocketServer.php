<?php

/**
 * Inane: Rebecca
 *
 * Inane WebSocket Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
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

namespace Inane\Rebecca;

use Inane\Rebecca\Command\Command;
use Inane\Stdlib\{
    Array\OptionsInterface,
    Json,
    Options};
use Swoole\{
    Http\Request,
    Http\Response,
    WebSocket\Frame,
    WebSocket\Server};
use WeakReference;

use function array_keys;

// use OpenSwoole\{
//     WebSocket\Frame,
//     WebSocket\Server,
//     Http\Request
// };

/**
 * WebSocket Server with Command Queue
 */
abstract class WebSocketServer {
    //#region Properties
    /**
     * Weak Instance Reference
     *
     * @var WeakReference
     */
    private static WeakReference $instance;
    private static array $clients = [];
    private Server $server;
    private array $commands = [];

    //#endregion Properties

    private function __construct(string $host = '0.0.0.0', int $port = 9501) {
        $this->server = new Server($host, $port);

        $this->server->set([
            'worker_num'                 => 2,
            'open_websocket_close_frame' => true,
            'open_websocket_ping_frame'  => true,
            //                    'task_worker_num' => 2,
        ]);

        $this->setupEventHandlers();
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
            static::$instance = WeakReference::create(new static(...$config->server->toArray()));
        }

        /**
         * @var WebSocketServer static::$instance->get()
         */
        return static::$instance->get();
    }

    /**
     * Setup event handlers
     */
    private function setupEventHandlers(): void {
        $this->server->on('start', function(Server $server) {
            echo "WebSocket Server started at ws://{$server->host}:{$server->port}\n";
            echo 'Available commands: ' . implode(', ', array_keys($this->commands)) . "\n";
        });

        $this->server->on('open', function(Server $server, Request $request) {
            // New clients start at rank 0 by default
            echo 'New client connected: ' . $request->fd . "\n";

            //            $client = new Rebecca($request->fd, $request->fd === 1 ? 0 : 10);
            $client = new Client($request->fd, 0);
            self::$clients[$request->fd] = $client;

            echo "Rebecca #{$request->fd} connected (Rank {$client->rank})\n";

            // Send a welcome message
            $server->push($request->fd, Json::encode([
                'command'            => 'welcome',
                'message'            => 'Connected to WebSocket server',
                'request'            => [
                    'structure' => [
                        'command' => 'command_name',
                        'data'    => '(optional) argument/parameter array',
                    ],
                ],
                'fd'                 => $request->fd,
                'rank'               => $client->rank,
                'available_commands' => array_map(function($cmd) {
                    return [
                        'name'        => $cmd->getName(),
                        'rank'        => $cmd->getRank(),
                        'description' => $cmd->getDescription(),
                    ];
                }, $this->commands),
            ]));
        });

        $this->server->on('message', function(Server $server, Frame $frame) {
            $event = new MessageEvent($this, $frame);

            echo "Received from #{$frame->fd}: {$frame->data}\n";

            // TODO: Handle `opcode` 0x09 (Ping) frames
            // if ($frame->opcode == 0x09) {
            //     $frame->data = ['type'];
            //     echo "Ping frame received: Code {$frame->opcode}\n";

            //     // Reply with Pong frame
            //     $pongFrame = new Frame;
            //     $pongFrame->opcode = WEBSOCKET_OPCODE_PONG;
            //     $server->push($frame->fd, $pongFrame);
            // }

            if ($err = $event->getError('client')) {
                $server->push($frame->fd, Json::encode($err));
                return;
            }

            try {
                if ($err = $event->getError('structure')) {
                    $server->push($frame->fd, Json::encode($err));
                    return;
                }

                if ($err = $event->getError('command')) {
                    $server->push($frame->fd, Json::encode($err));
                    return;
                }

                // Check if a client has high enough rank to execute a command
                if (!$event->client->canExecuteCommand($event->command)) {
                    $server->push($frame->fd, Json::encode([
                        'error'         => "Insufficient rank to execute command: {$event->commandName}",
                        'required_rank' => $event->command->getRank(),
                        'your_rank'     => $event->client->getRank(),
                    ]));

                    return;
                }

                // Execute the command
                $event->executeCommand();
            } catch (\Exception $e) {
                $server->push($frame->fd, Json::encode(['error' => 'Error processing command: ' . $e->getMessage(),]));
            }
        });

        $this->server->on('close', function(Server $server, int $fd) {
            echo "Rebecca #{$fd} disconnected\n";
            unset(self::$clients[$fd]);
        });

        $this->server->on('request', function(Request $request, Response $response) {
            // Receive HTTP request and get the value of 'message' from get, then push it to users
            // Loop through all websocket connections' fds and push to all users

            // $server = static::$instance->get()->server;

            // $server->connections traverse all websocket connection users' fds, push to all users
            foreach($this->server->connections as $fd) {
                // Need to check if it is a correct websocket connection, otherwise pushing may fail
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });
    }

    #region Command Management
    public function commands(): array {
        return array_keys($this->commands);
    }
    /**
     * Register a command
     */
    public function registerCommand(Command $command, bool $replace = false): void {
        if (!$this->hasCommand($command->getName()) || $replace) {
            $this->commands[$command->getName()] = $command;
            echo "Registered command: {$command->getName()} (Rank {$command->getRank()})\n";
        }
    }

    public function hasCommand(string $commandName): bool {
        return isset($this->commands[$commandName]);
    }

    public function getCommand(string $commandName): ?Command {
        return $this->hasCommand($commandName) ? $this->commands[$commandName] : null;
    }
    #endregion Command Management

    /**
     * Get a client by file descriptor
     */
    public function getClient(int $fd): ?Client {
        return array_find(self::$clients, static fn($client) => $client->fd === $fd);
    }

    public function getClientByFrame(Frame $frame): ?Client {
        return self::$clients[$frame->fd] ?? null;
    }

    /**
     * Start the server
     */
    public function start(): void {
        $this->server->start();
    }

    /**
     * Get the underlying server instance
     */
    public function getServer(): Server {
        return $this->server;
    }
}
