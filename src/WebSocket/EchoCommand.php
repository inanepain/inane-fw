<?php
/*
 *
 *  Inane: PROJECT
 *
 *  PROJECT_DESCRIPTION
 *
 *  $Id$
 *  $Date$
 *
 *  PHP version 8.5
 *
 *  @author   Philip Michael Raab <philip@cathedral.co.za>
 *  @package  inanepain\PROJECT
 *  @category PROJECT
 *
 *  @license  UNLICENSE
 *  @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 *  _version_ $version
 *
 */

namespace Knot\WebSocket;

use Inane\Rebecca\{
    Client,
    Command\Command};
use Inane\Stdlib\{
    Array\OptionsInterface,
    Json,
    Options};
use Swoole\WebSocket\Server;
use Throwable;

/**
 * Example: Echo Command (Rank 0 - Available to all)
 */
class EchoCommand extends Command {
    /**
     * Constructs an instance of the class.
     *
     * This constructor calls the parent's constructor with a default argument to ensure proper initialization
     * and inheritance behavior. It sets up any necessary initial state for new objects without requiring additional input from users.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Returns a constant representing an action or command in PHP scripting language that outputs data to some output stream such as STDOUT.
     *
     * This method doesn't accept any parameters and simply returns a predefined static value associated with the echo operation
     * within the context of this class's functionality. It is used for identifying purposes rather than executing
     * an actual command or action at runtime when called in different scenarios where such identification may be necessary.
     *
     * @return string 'echo'
     */
    public function getName(): string {
        return 'echo';
    }

    /**
     * Retrieves a description of an entity or concept by returning its associated textual representation as a string.
     *
     * This method is designed to provide human-readable descriptions that are convenient for display purposes,
     * logging, error messages, user interfaces, etc., abstracting away any complex internal representations
     * used within the system. The returned strings can be formatted and used according to application requirements.
     *
     * @return string A description of an entity or concept.
     */
    public function getDescription(): string {
        return 'Echoes back the message';
    }

    /**
     * Executes the echo command by sending a JSON-encoded message to the client.
     *
     * @param Server                      $server The server instance used to push data.
     * @param Client                      $client The client instance whose file descriptor will receive the message.
     * @param null|array|OptionsInterface $data   Optional data containing a 'message' key.
     *
     * @return void
     *
     * @throws Throwable If encoding the JSON fails or if an invalid $data type is provided.
     */
    public function execute(Server $server, Client $client, null|array|OptionsInterface $data = null): void {
        if (! $data instanceof OptionsInterface)
            $data = new Options($data);

        $server->push($client->fd, Json::encode([
            'command' => 'echo',
            'message' => $data['message'] ?? 'No message provided',
        ]));
    }
}
