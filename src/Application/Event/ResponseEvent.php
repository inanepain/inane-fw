<?php

declare(strict_types=1);

namespace Knot\Application\Event;

use Inane\Event\StoppableEvent;
use Inane\Http\Response;

/**
 * Response lifecycle event.
 */
final class ResponseEvent extends StoppableEvent {
    private bool $handled = false;

    /**
     * @param Response $response Response to send.
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
