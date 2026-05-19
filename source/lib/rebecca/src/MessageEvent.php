<?php

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

namespace Inane\Rebecca;

use Inane\Rebecca\Command\Command;
use Inane\Stdlib\Array\OptionsInterface;
use Inane\Stdlib\Exception\JsonException;
use Inane\Stdlib\Json;
use Inane\Stdlib\Options;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

use function array_key_exists;
use function array_keys;
use function is_string;
use function json_validate;
use function preg_match;

use const PHP_EOL;

/**
 * Represents a WebSocket message event that processes incoming data, validates its structure,
 * links it to a client, verifies the user, and executes the associated command.
 */
class MessageEvent {
    /**
     * Collected errors during bootstrap/verification steps.
     *
     * @var array<string, mixed>
     */
    private array $errors = [];

    /**
     * Swoole WebSocket server instance.
     *
     * Lazily resolved from the current {@see WebSocketServer} instance and cached.
     */
    protected Server $server {
        get => $this->server ?? $this->server = $this->wss->getServer();
    }

    /**
     * The client associated with the incoming frame.
     *
     * Lazily resolved from the current {@see WebSocketServer} by using the provided frame.
     *
     * @var Client|null
     */
    public ?Client $client {
        get => $this->client ?? $this->client = $this->wss->getClientByFrame($this->frame);
    }

    /**
     * Parsed packet wrapper around the raw frame payload.
     *
     * Lazily created from {@see Frame::$data}.
     */
    protected Options $packet {
        get => $this->packet ?? $this->packet = new Options($this->frame->data);
    }

    /**
     * Command name extracted from the packet.
     */
    public string $commandName {
        get => $this->commandName ?? $this->commandName = $this->packet->command;
    }

    /**
     * Resolved command implementation for {@see MessageEvent::$commandName}.
     */
    public Command $command {
        get => $this->command ?? $this->command = $this->wss->getCommand($this->commandName);
    }

    /**
     * Command payload (`data`) from the packet.
     *
     * Defaults to an empty {@see Options} instance when not present.
     */
    protected OptionsInterface $data {
        get => $this->data ?? $this->data = $this->packet->get('data', new Options());
    }

    /**
     * @param WebSocketServer $wss   WebSocket server wrapper.
     * @param Frame           $frame Incoming WebSocket frame.
     *
     * @throws JsonException When the packet structure must be normalized to JSON and encoding fails.
     */
    public function __construct(protected WebSocketServer $wss, protected Frame $frame) {
        $this->bootstrap();
    }

    /**
     * Executes the resolved command.
     *
     * @return void
     */
    public function executeCommand(): void {
        $this->command->execute($this->server, $this->client, $this->data);
    }

    /**
     * Writes a log message.
     *
     * @param string $message
     *
     * @return void
     */
    protected static function log(string $message): void {
        echo $message;
    }

    /**
     * Initializes the bootstrap process by sequentially verifying the structure, client, user, and command.
     *
     * @return void
     *
     * @throws JsonException
     */
    private function bootstrap(): void {
        if (!$this->verifyStructure()) return;

        if (!$this->verifyClient()) return;
        $this->verifyUser();

        if (!$this->verifyCommand()) return;
    }

    /**
     * Verifies the structure of the data in the frame, ensuring it is in a valid format.
     * If the data is not a valid JSON string but matches a specific pattern, it will be converted into a JSON-encoded format.
     * Any structural errors are logged in the `errors` property.
     *
     * @return bool Returns true if the structure is valid or successfully adjusted; false if there are structural errors.
     *
     * @throws JsonException
     */
    private function verifyStructure(): bool {
        if (is_string($this->frame->data)) {
            if (!json_validate($this->frame->data)) {
                if (preg_match('/^[a-zA-Z][a-zA-Z0-9]+$/', $this->frame->data, $matches) === 1) {
                    $this->frame->data = Json::encode(['command' => $matches[0]]);
                } else {
                    $this->errors['structure'] = ['error' => 'Invalid format. Expected JSON with "command" field'];
                }
            }
        }

        return !array_key_exists('structure', $this->errors);
    }

    /**
     * Ensures the incoming frame maps to a currently connected client.
     *
     * @return bool Returns true when a client was resolved; false otherwise.
     */
    private function verifyClient(): bool {
        if (!$this->client) {
            $this->errors['client'] = ['error' => 'Client not found in active connections.'];
        }

        return !array_key_exists('client', $this->errors);
    }

    /**
     * Associates a user with the resolved client if the packet contains a `user` field.
     *
     * @return void
     */
    private function verifyUser(): void {
        if ($this->packet->offsetExists('user') && !$this->client->has('user')) {
            $this->client->set('user', $this->packet->offsetGet('user'));

            static::log('USER: ' . $this->client->get('user') . ' SETs' . PHP_EOL);
        }
    }

    /**
     * Verifies that the requested command exists on the server.
     *
     * @return bool Returns true when the command exists; false otherwise.
     */
    private function verifyCommand(): bool {
        if (!$this->wss->hasCommand($this->commandName)) {
            $this->errors['command'] = [
                'error'              => "Unknown command: {$this->commandName}",
                'available_commands' => array_keys($this->wss->commands()),
            ];
        }

        return !array_key_exists('command', $this->errors);
    }

    /**
     * Returns a previously collected error by type.
     *
     * @param string $errorType Error key (e.g. `structure`, `client`, `command`).
     *
     * @return array|null
     */
    public function getError(string $errorType): ?array {
        if (array_key_exists($errorType, $this->errors)) {
            return $this->errors[$errorType];
        }

        return null;
    }
}
