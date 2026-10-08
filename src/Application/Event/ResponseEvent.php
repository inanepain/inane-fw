<?php

/**
 * Framework
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\fw
 * @category fw
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Knot\Application\Event;

use Inane\Event\StoppableEvent;
use Inane\Http\Response;

/**
 * Response lifecycle event.
 */
final class ResponseEvent extends StoppableEvent {
    /**
     * @var bool $handled Indicates whether the operation has been handled.
     */
    private bool $handled = false;

    /**
     * Constructor method for initialising the class with a Response instance.
     *
     * @param Response $response The response instance to be used within the class.
     *
     * @return void
     */
    public function __construct(public Response $response) {}

    /**
     * Marks the response as handled by a listener.
     *
     * @return void
     */
    public function markHandled(): void {
        $this->handled = true;
    }

    /**
     * Whether a listener has handled the response.
     *
     * @return bool
     */
    public function isHandled(): bool {
        return $this->handled;
    }
}
