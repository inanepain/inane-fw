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
use Swoole\WebSocket\Server;

/**
 * Example: Broadcast Command (Rank 5 - Moderator level)
 */
class BroadcastCommand extends Command {
    public function __construct() {
        parent::__construct(5);
    }

    public function getName(): string {
        return 'broadcast';
    }

    public function getDescription(): string {
        return 'Broadcasts a message to all connected clients';
    }

    public function execute(Server $server, Client $client, null|array|OptionsInterface $data = null): void {
        if (! $data instanceof OptionsInterface)
            $data = new Options($data);

        $message = $data['message'] ?? 'No message';
        $payload = Json::encode([
            'command' => 'broadcast',
            'from'    => $client->fd,
            'user'    => $client?->get('user'),
            'data'    => $data->toArray(),
        ]);

        foreach($server->connections as $fd) {
            $server->push($fd, $payload);
        }
    }
}
