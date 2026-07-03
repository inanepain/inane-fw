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

/**
 * Example: Echo Command (Rank 0 - Available to all)
 */
class EchoCommand extends Command {
    /**
     *
     * @return void
     *
     */
    public function __construct() {
        parent::__construct(0);
    }

    /**
     *
     * @return string
     *
     */
    public function getName(): string {
        return 'echo';
    }

    /**
     *
     * @return string
     *
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
     * @throws \Throwable If encoding the JSON fails or if an invalid $data type is provided.
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
