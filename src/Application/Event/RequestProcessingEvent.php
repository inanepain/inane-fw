<?php

declare(strict_types=1);

namespace Knot\Application\Event;

use Inane\Event\StoppableEvent;
use Inane\Http\Request;

/**
 * Request-processing lifecycle event.
 */
final class RequestProcessingEvent extends StoppableEvent {
    /**
     * @param Request $request Request to process.
     *
     * @return void
     */
    public function __construct(public Request $request) {}
}
