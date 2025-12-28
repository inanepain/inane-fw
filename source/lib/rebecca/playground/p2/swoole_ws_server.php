<?php

use OpenSwoole\WebSocket\Server;
use OpenSwoole\WebSocket\Frame;

/**
 * Rebecca class to manage individual WebSocket connections
 */
class Client {
    public int $fd;
    public array $data = [];
    public float $connectedAt;

    public function __construct(int $fd) {
        $this->fd = $fd;
        $this->connectedAt = microtime(true);
    }

    public function set(string $key, $value): void {
        $this->data[$key] = $value;
    }

    public function get(string $key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool {
        return isset($this->data[$key]);
    }
}

/**
 * Abstract Command class that all commands must extend
 */
abstract class Command {
    abstract public function getName(): string;
    abstract public function execute(Server $server, Client $client, array $data): void;

    public function getDescription(): string {
        return 'No description provided';
    }
}

/**
 * Example: Ping Command
 */
class PingCommand extends Command {
    public function getName(): string {
        return 'ping';
    }

    public function getDescription(): string {
        return 'Responds with pong';
    }

    public function execute(Server $server, Client $client, array $data): void {
        $server->push($client->fd, json_encode([
            'command' => 'pong',
            'timestamp' => microtime(true)
        ]));
    }
}

/**
 * Example: Echo Command
 */
class EchoCommand extends Command {
    public function getName(): string {
        return 'echo';
    }

    public function getDescription(): string {
        return 'Echoes back the message';
    }

    public function execute(Server $server, Client $client, array $data): void {
        $server->push($client->fd, json_encode([
            'command' => 'echo',
            'message' => $data['message'] ?? 'No message provided'
        ]));
    }
}

/**
 * Example: Broadcast Command
 */
class BroadcastCommand extends Command {
    public function getName(): string {
        return 'broadcast';
    }

    public function getDescription(): string {
        return 'Broadcasts a message to all connected clients';
    }

    public function execute(Server $server, Client $client, array $data): void {
        $message = $data['message'] ?? 'No message';
        $payload = json_encode([
            'command' => 'broadcast',
            'from' => $client->fd,
            'message' => $message
        ]);

        foreach ($server->connections as $fd) {
            $server->push($fd, $payload);
        }
    }
}

/**
 * Example: Info Command
 */
class InfoCommand extends Command {
    public function getName(): string {
        return 'info';
    }

    public function getDescription(): string {
        return 'Returns client information';
    }

    public function execute(Server $server, Client $client, array $data): void {
        $server->push($client->fd, json_encode([
            'command' => 'info',
            'fd' => $client->fd,
            'connected_at' => $client->connectedAt,
            'uptime' => microtime(true) - $client->connectedAt,
            'data' => $client->data
        ]));
    }
}

/**
 * WebSocket Server with Command Queue
 */
class WebSocketServer {
    private Server $server;
    private array $commands = [];
    private array $clients = [];

    public function __construct(string $host = '0.0.0.0', int $port = 9501) {
        $this->server = new Server($host, $port);

        $this->server->set([
            'worker_num' => 2,
            'task_worker_num' => 2,
        ]);

        $this->setupEventHandlers();
    }

    /**
     * Register a command
     */
    public function registerCommand(Command $command): void {
        $this->commands[$command->getName()] = $command;
        echo "Registered command: {$command->getName()}\n";
    }

    /**
     * Setup event handlers
     */
    private function setupEventHandlers(): void {
        $this->server->on('start', function (Server $server) {
            echo "WebSocket Server started at ws://{$server->host}:{$server->port}\n";
            echo "Available commands: " . implode(', ', array_keys($this->commands)) . "\n";
        });

        $this->server->on('open', function (Server $server, $request) {
            $client = new Client($request->fd);
            $this->clients[$request->fd] = $client;

            echo "Rebecca #{$request->fd} connected\n";

            // Send welcome message
            $server->push($request->fd, json_encode([
                'command' => 'welcome',
                'message' => 'Connected to WebSocket server',
                'fd' => $request->fd,
                'available_commands' => array_keys($this->commands)
            ]));
        });

        $this->server->on('message', function (Server $server, Frame $frame) {
            echo "Received from #{$frame->fd}: {$frame->data}\n";

            $client = $this->clients[$frame->fd] ?? null;
            if (!$client) {
                $server->push($frame->fd, json_encode([
                    'error' => 'Rebecca not found'
                ]));
                return;
            }

            try {
                $data = json_decode($frame->data, true);

                if (!$data || !isset($data['command'])) {
                    $server->push($frame->fd, json_encode([
                        'error' => 'Invalid format. Expected JSON with "command" field'
                    ]));
                    return;
                }

                $commandName = $data['command'];

                if (!isset($this->commands[$commandName])) {
                    $server->push($frame->fd, json_encode([
                        'error' => "Unknown command: {$commandName}",
                        'available_commands' => array_keys($this->commands)
                    ]));
                    return;
                }

                // Execute the command
                $this->commands[$commandName]->execute($server, $client, $data);
            } catch (\Exception $e) {
                $server->push($frame->fd, json_encode([
                    'error' => 'Error processing command: ' . $e->getMessage()
                ]));
            }
        });

        $this->server->on('close', function (Server $server, int $fd) {
            echo "Rebecca #{$fd} disconnected\n";
            unset($this->clients[$fd]);
        });
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

// Initialize and start the server
$wsServer = new WebSocketServer('0.0.0.0', 9501);

// Register commands
$wsServer->registerCommand(new PingCommand());
$wsServer->registerCommand(new EchoCommand());
$wsServer->registerCommand(new BroadcastCommand());
$wsServer->registerCommand(new InfoCommand());

// Start the server
$wsServer->start();
