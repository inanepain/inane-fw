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

namespace Inane\Rebecca\Command;

use Inane\Rebecca\Client;
use Inane\Stdlib\Array\OptionsInterface;
use Swoole\WebSocket\Server;

/**
 * Abstract Command class that all commands must extend
 */
abstract class Command {
    //#region Properties
    protected int $rank;
    //#endregion Properties

    /**
     * Constructor method to initialize the object with a rank value.
     *
     * @param int $rank The rank to initialize the object with. Defaults to 0.
     *
     * @return void
     */
    public function __construct(int $rank = 0) {
        $this->rank = $rank;
    }

    /**
     * Abstract method to retrieve the name.
     *
     * @return string The name as a string.
     */
    abstract public function getName(): string;

    /**
     * Executes an operation using the provided server, client, and optional data.
     *
     * @param Server                      $server The server instance to execute the operation on.
     * @param Client                      $client The client instance associated with the operation.
     * @param null|array|OptionsInterface $data   Optional data or options to be used during the execution. Defaults to null.
     *
     * @return void
     */
    abstract public function execute(Server $server, Client $client, null|array|OptionsInterface $data = null): void;

    /**
     * Retrieves the description for the current object.
     *
     * @return string The description of the object.
     */
    public function getDescription(): string {
        return 'No description provided';
    }

    /**
     * Retrieves the rank value of the object.
     *
     * @return int The current rank of the object.
     */
    public function getRank(): int {
        return $this->rank;
    }
}
