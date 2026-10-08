<?php

declare(strict_types=1);

namespace Knot\Application\Event;

use Inane\Event\StoppableEvent;
use Inane\Http\Request;
use Inane\Routing\RouteMatch;

/**
 * Routing lifecycle event.
 */
final class RoutingEvent extends StoppableEvent {
    /**
     * @param Request $request Request to route.
     * @param RouteMatch|null $routeMatch Optional route supplied by a listener.
     *
     * @return void
     */
    public function __construct(
        public Request $request,
        public ?RouteMatch $routeMatch = null,
    ) {}
}
