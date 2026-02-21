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
    Command\Command,
    WebSocketServer};
use Inane\Stdlib\{
    Array\OptionsInterface,
    Json,
    Options};
use Swoole\WebSocket\Server;

/**
 * Example: Info Command (Rank 0 - Available to all)
 */
class InfoCommand extends Command {
//#region Properties
    protected array $defaults = ['fd' => null];
//#endregion Properties

    public function __construct() {
        parent::__construct(0);
    }

    public function getName(): string {
        return 'info';
    }

    public function getDescription(): string {
        return 'Returns client information';
    }

    /**
     * Executes the command
     *
     * @param \Swoole\WebSocket\Server $server
     * @param \Inane\Rebecca\Client $client
     * @param null|array|\Inane\Stdlib\Array\OptionsInterface $data
     * @return void
     */
    public function execute(Server $server, Client $client, null|array|OptionsInterface $data = null): void {
        if (! $data instanceof OptionsInterface)
            $data = new Options($data);

        $data->complete($this->defaults);
        $info = $client;

        //        WebSocketServer::init()
        foreach($server->connections as $fd) {
            $cl = WebSocketServer::init()
                ->getClient($fd)
            ;
            //            var_dump($cl);
        }

        if ($data->fd) {
            $info = WebSocketServer::init()
                ->getClient($data->fd)
            ;
        }

        $server->push($client->fd, Json::encode([
            'command' => 'info',
            'data'    => [
                'fd'           => $info->fd,
                'rank'         => $info->rank,
                'connected_at' => $info->connectedAt,
                'uptime'       => microtime(true) - $info->connectedAt,
            ],
            'result'  => $client->data,
        ]));
    }
}
