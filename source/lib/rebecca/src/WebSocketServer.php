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
use Inane\Stdlib\Array\OptionsInterface;
use Inane\Stdlib\Json;
use Inane\Stdlib\Options;
use OpenSwoole\Http\Request;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\WebSocket\Server;
use WeakReference;

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
            'worker_num' => 2,
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
            echo "Available commands: " . implode(', ', array_keys($this->commands)) . "\n";
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
            echo "Received from #{$frame->fd}: {$frame->data}\n";

            /**
             * @var Client|null $client
             */
            $client = self::$clients[$frame->fd] ?? null;
            if (!$client) {
                $server->push($frame->fd, Json::encode([
                    'error' => 'Rebecca not found',
                ]));

                return;
            }

            try {
                $packet = new Options($frame->data);

                if ($packet->offsetExists('user') && !$client->has('user')) {
                    $client->set('user', $packet->offsetGet('user'));
                    echo 'USER: ' . $client->get('user') . ' SETs' . PHP_EOL;
                }

                $data = $packet->data;
                if (!$packet || !$packet->offsetExists('command')) {
                    $server->push($frame->fd, Json::encode([
                        'error' => 'Invalid format. Expected JSON with "command" field',
                    ]));

                    return;
                }

                $commandName = $packet->command;
                if (!isset($this->commands[$commandName])) {
                    $server->push($frame->fd, Json::encode([
                        'error'              => "Unknown command: {$commandName}",
                        'available_commands' => array_keys($this->commands),
                    ]));

                    return;
                }

                $command = $this->commands[$commandName];
                // Check if a client has sufficient rank to execute a command
                if (!$client->canExecuteCommand($command)) {
                    $server->push($frame->fd, Json::encode([
                        'error'         => "Insufficient rank to execute command: {$commandName}",
                        'required_rank' => $command->getRank(),
                        'your_rank'     => $client->getRank(),
                    ]));

                    return;
                }

                // Execute the command
                $command->execute($server, $client, $data);
            } catch (\Exception $e) {
                $server->push($frame->fd, Json::encode([
                    'error' => 'Error processing command: ' . $e->getMessage(),
                ]));
            }
        });

        $this->server->on('close', function(Server $server, int $fd) {
            echo "Rebecca #{$fd} disconnected\n";
            unset(self::$clients[$fd]);
        });
    }

    /**
     * Register a command
     */
    public function registerCommand(Command $command): void {
        $this->commands[$command->getName()] = $command;
        echo "Registered command: {$command->getName()} (Rank {$command->getRank()})\n";
    }

    /**
     * Get a client by file descriptor
     */
    public function getClient(int $fd): ?Client {
        return array_find(self::$clients, static fn($client) => $client->fd === $fd);
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
