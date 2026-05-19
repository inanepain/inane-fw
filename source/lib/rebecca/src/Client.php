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

use Inane\Config\Config;
use Inane\Db\Adapter\Adapter;
use Inane\IdForge\IdGeneratorFactory;
use Inane\Rebecca\Command\Command;
use Knot\Db\Entity\User;
use Knot\Db\Table\UsersTable;

/**
 * Rebecca class to manage individual WebSocket connections
 */
class Client {
    //#region Properties
    public int $fd;
    public array $data = [];
    public float $connectedAt;
    public int $rank;
    public readonly string $name;
    public readonly User $identity;

    //#endregion Properties

    public function __construct(int $fd, int $rank = 0) {
        $this->name = IdGeneratorFactory::createULID()
            ->generate()
        ;
        $this->fd = $fd;
        $this->rank = $rank;
        $this->connectedAt = microtime(true);
    }

    protected function hydrate(): void {
        UsersTable::$db = new Adapter(Config::fromConfigFile()
                                          ->get('db'));
        $ut = new UsersTable();
        $result = $ut->search(['username' => $this->get('user')]);

        if (!empty($result)) {
            $this->identity = array_shift($result);
            $this->identity->online = 1;
            $this->identity->save();
        }
    }

    public function __destruct() {
        // $this->identity->online = 0;
        // $this->identity->save();
        if (isset($this->identity)) {
            $user = $this->identity;
            $user->online = 0;
            $user->save();
        }
    }

    public function set(string $key, $value): void {
        $this->data[$key] = $value;

        if ($key === 'user') $this->hydrate();
    }

    public function get(string $key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool {
        return isset($this->data[$key]);
    }

    public function getRank(): int {
        return $this->rank;
    }

    public function setRank(int $rank): void {
        $this->rank = $rank;
    }

    public function canExecuteCommand(Command $command): bool {
        return $this->rank <= $command->getRank();
    }
}
