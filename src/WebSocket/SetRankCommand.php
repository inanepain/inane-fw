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
    Command\Command,
    WebSocketServer};
use Inane\Stdlib\{
    Array\OptionsInterface,
    Exception\JsonException,
    Json,
    Options};
use InvalidArgumentException;
use Swoole\WebSocket\Server;

/**
 * Example: Set Rank Command (Rank 10 - Admin level)
 */
class SetRankCommand extends Command {
    /**
     *
     */
    public function __construct() {
        parent::__construct(10);
    }

    /**
     * Retrieves the name of the command.
     *
     * @return string The command name, which is 'setrank'.
     */
    public function getName(): string {
        return 'setrank';
    }

    /**
     * Returns a brief description of the command.
     *
     * @return string The command description, used for help or logging purposes.
     */
    public function getDescription(): string {
        return 'Sets the rank of a client (admin only)';
    }

    /**
     * Executes the set‑rank command.
     *
     * @param Server                      $server The server instance used to send responses and broadcast updates.
     * @param Client                      $client The client that initiated the command.
     * @param null|array|OptionsInterface $data   Optional data array or OptionsInterface instance containing the target file descriptor and new rank.
     *
     * @return void
     *
     * @throws InvalidArgumentException|JsonException If the provided data cannot be converted into an OptionsInterface instance.
     */
    public function execute(Server $server, Client $client, null|array|OptionsInterface $data = null): void {
        if (! $data instanceof OptionsInterface)
            $data = new Options($data);

        $targetFd = $data['fd'] ?? null;
        $newRank = $data['rank'] ?? null;

        if ($targetFd === null || $newRank === null) {
            $server->push($client->fd, Json::encode([
                'error' => 'Missing required fields: fd and rank',
            ]));

            return;
        }

        // Get the WebSocketServer instance to access clients
        $wsServer = WebSocketServer::init();
        $targetClient = $wsServer->getClient($targetFd);

        if (!$targetClient) {
            $server->push($client->fd, Json::encode([
                'error' => "Rebecca #{$targetFd} not found",
            ]));

            return;
        }

        $oldRank = $targetClient->getRank();
        $targetClient->setRank((int)$newRank);

        $server->push($client->fd, Json::encode([
            'command'   => 'setrank',
            'success'   => true,
            'target_fd' => $targetFd,
            'old_rank'  => $oldRank,
            'new_rank'  => $newRank,
        ]));

        // Notify the target client
        $server->push($targetFd, Json::encode([
            'command'  => 'rank_updated',
            'old_rank' => $oldRank,
            'new_rank' => $newRank,
        ]));
    }
}
