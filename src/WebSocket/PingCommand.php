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
 *  * PHP version 8.4
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
    public const int RETURN_JSON = 0;
    public const int RETURN_FRAME = 1;

    public static int $returnType = self::RETURN_JSON;

    public function __construct() {
        parent::__construct(0);
    }

    public function getName(): string {
        return 'ping';
    }

    public function getDescription(): string {
        return 'Responds with pong';
    }

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
