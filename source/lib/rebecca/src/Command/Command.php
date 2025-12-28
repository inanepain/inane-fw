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

namespace Inane\Rebecca\Command;

use Inane\Rebecca\Client;
use OpenSwoole\WebSocket\Server;

/**
 * Abstract Command class that all commands must extend
 */
abstract class Command {
//#region Properties
    protected int $rank;
//#endregion Properties

    public function __construct(int $rank = 0) {
        $this->rank = $rank;
    }

    abstract public function getName(): string;

    abstract public function execute(Server $server, Client $client, array $data): void;

    public function getDescription(): string {
        return 'No description provided';
    }

    public function getRank(): int {
        return $this->rank;
    }
}
