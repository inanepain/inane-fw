<?php
/*
 * *
 *  * Inane: PROJECT
 *  *
 *  * PROJECT_DESCRIPTION
 *  *
 *  * $Id$
 *  * $Date$
 *  *
 *  * PHP version 8.5
 *  *
 *  * @author   Philip Michael Raab <philip@cathedral.co.za>
 *  * @package  inanepain\PROJECT
 *  * @category PROJECT
 *  *
 *  * @license  UNLICENSE
 *  * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *  *
 *  * _version_ $version
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
use Swoole\WebSocket\{
    Frame,
    Server};

/**
 * Example: Ping Command (Rank 0 - Available to all)
 */
class PingCommand extends Command {
    /**
     * Indicates that the response should be returned in JSON format.
     *
     * @var int
     */
    public const int RETURN_JSON = 0;

    /**
     * Constant used to indicate a return frame.
     *
     * @var int
     */
    public const int RETURN_FRAME = 1;

    /**
     * Indicates that the response should be returned in JSON format.
     *
     * @var int
     */
    public static int $returnType = self::RETURN_JSON;

    /**
     *
     */
    public function __construct() {
        parent::__construct(0);
    }

    /**
     * Returns the name of the command.
     *
     * @return string
     */
    public function getName(): string {
        return 'ping';
    }

    /**
     * Returns the description of the command.
     *
     * @return string
     */
    public function getDescription(): string {
        return 'Responds with pong';
    }

    /**
     * Executes a pong response for the given client.
     *
     * @param Server                      $server The server instance used to push data back to the client.
     * @param Client                      $client The client connection for which the pong response is generated.
     * @param null|array|OptionsInterface $data   Optional data used to determine the type of pong response.
     *
     * @return void
     */
    public function execute(Server $server, Client $client, null|array|OptionsInterface $data = null): void {
        if (! $data instanceof OptionsInterface)
            $data = new Options($data);

        $type = $data->type ?? self::$returnType;

        $pongFrame = match ($type) {
            self::RETURN_FRAME => (static function (): Frame {
                $f = new Frame;
                $f->opcode = WEBSOCKET_OPCODE_PONG;
                return $f;
            })(),
            default => Json::encode([
                'command'   => 'pong',
                'timestamp' => microtime(true),
            ]),
        };

        $server->push($client->fd, $pongFrame);
    }
}
