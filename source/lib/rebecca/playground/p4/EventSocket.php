<?php
/*
 * This is free and unencumbered software released into the public domain.
 *
 * Anyone is free to copy, modify, publish, use, compile, sell, or
 * distribute this software, either in source code form or as a compiled
 * binary, for any purpose, commercial or non-commercial, and by any
 * means.
 *
 * In jurisdictions that recognize copyright laws, the author or authors
 * of this software dedicate any and all copyright interest in the
 * software to the public domain. We make this dedication for the benefit
 * of the public at large and to the detriment of our heirs and
 * successors. We intend this dedication to be an overt act of
 * relinquishment in perpetuity of all present and future rights to this
 * software under copyright law.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * For more information, please refer to <http://unlicense.org/>
 */

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
 * @author Philip Michael Raab <philip@cathedral.co.za>
 * @package inanepain\rebecca
 * @category websocket
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace p4;

use Inane\Stdlib\Array\OptionsInterface;
use Inane\Stdlib\Object\MagicPropertyTrait;
use Inane\Stdlib\Options;
use OpenSwoole\WebSocket\Server;

use function is_array;

/**
 * EventSocket
 *
 * Simple data carrier for websocket events. Stores commonly used event
 * attributes such as server, id, name, command, request and data in an
 * `OptionsInterface` container while providing convenient getters.
 *
 * The class leverages `MagicPropertyTrait` for optional magic access and keeps
 * internal storage flexible by accepting either an array or an
 * `OptionsInterface` as input.
 */
class EventSocket {
    use MagicPropertyTrait;

    /**
     * Event details storage.
     *
     * Keys expected:
     * - `server`
     * - `id`
     * - `name`
     * - `command`
     * - `request`
     * - `data`
     */
    protected OptionsInterface $details {
        get => $this->details;
        set(array|OptionsInterface $value) => $this->details = is_array($value) ? new Options($value) : $value;
    }

    /**
     * Constructor
     *
     * @param array<string,mixed>|OptionsInterface $data Initial event details.
     */
    public function __construct(array|OptionsInterface $data) {
        // static::$MAGIC_PROPERTY_GET = '';
        // static::$MAGIC_PROPERTY_SET = '';
        $this->details = $data;
        static::$verify = false;
    }

    /**
     * Factory: create an EventSocket from discrete values.
     *
     * @param Server $server  Server identifier or instance reference
     * @param int $id      Event identifier
     * @param string $name    Event name
     * @param null|string $command Command/action name
     * @param mixed $request Associated request payload or object
     * @param null|OptionsInterface $data    Arbitrary event data
     *
     * @return self
     */
    public static function createEvent(Server $server, int $id, string $name, ?string $command, $request, ?OptionsInterface $data) {
        return new self([
            'server'  => $server,
            'id'      => $id,
            'name'    => $name,
            'command' => $command,
            'request' => $request,
            'data'    => $data,
        ]);
    }

    /**
     * Get the value of `id`.
     *
     * @return int
     */
    public function getId(): int {
        return $this->details['id'];
    }

    /**
     * Get the value of `name`.
     *
     * @return string
     */
    public function getName(): string {
        return $this->details['name'];
    }

    /**
     * Get the value of `command`.
     *
     * @return null|string
     */
    public function getCommand(): ?string {
        return $this->details['command'];
    }

    /**
     * Get the value of `server`.
     *
     * @return Server
     */
    public function getServer(): Server {
        return $this->details['server'];
    }

    /**
     * Get the value of `request`.
     *
     * @return mixed
     */
    public function getRequest() {
        return $this->details['request'];
    }

    /**
     * Get the value of `data`.
     *
     * @return null|OptionsInterface
     */
    public function getData(): ?OptionsInterface {
        return $this->details['data'];
    }
}
