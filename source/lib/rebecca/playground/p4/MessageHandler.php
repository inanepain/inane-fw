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

use Inane\Stdlib\Json;

use function time;

use const PHP_EOL;

/**
 * Class MessageHandler
 *
 * Handles events for a single WebSocket connection identified by $id.
 */
class MessageHandler {
    /**
     * Construct a new handler for the given connection id.
     *
     * @param int $id Connection file descriptor / id
     */
    public function __construct(
        protected int $id
    ) {
    }

    /**
     * Default index method — placeholder for future commands.
     *
     * Currently, writes a simple debug message to stdout.
     *
     * @return void
     */
    public function index(): void {
        // simple debug output identifying this handler
        echo "MessageHandler: {$this->id}: index";
    }

    /**
     * Handle a connection open event.
     *
     * Sends an initial hello message back to the client and logs to stdout.
     *
     * @param EventSocket $event Event details (contains server instance and fd)
     *
     * @return void
     */
    public function open(EventSocket $event): void {
        // log connection open
        echo "connection open: {$event->id}" . PHP_EOL;

        // send a JSON encoded greeting with a timestamp
        $event->server->push($event->id, Json::encode(['hello', time()]));

        // NOTE: uncomment below to start a periodic tick that pushes data
        // $event->server->tick(1000, function () use ($event) {
        //     $event->server->push($event->request->fd, json_encode(["hello", time()]));
        // });
    }

    /**
     * Handle a connection close event.
     *
     * Logs the close event; any cleanup for the connection could be added here.
     *
     * @param EventSocket $event Event details (contains server instance and fd)
     *
     * @return void
     */
    public function close(EventSocket $event): void {
        // log connection close
        echo "connection closed: {$event->id}" . PHP_EOL;

        // Place for any teardown logic if required in the future.
        // $event->server->push($event->id, json_encode(["goodbye", time()]));
    }
}
