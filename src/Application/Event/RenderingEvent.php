<?php

declare(strict_types=1);

namespace Knot\Application\Event;

use Inane\Event\StoppableEvent;
use Inane\Http\Request;
use Inane\Http\Response;
use Inane\Routing\RouteMatch;

/**
 * Rendering lifecycle event.
 */
final class RenderingEvent extends StoppableEvent {
    /**
     * @param Request $request Request being rendered.
     * @param RouteMatch $routeMatch Matched route.
     * @param Response|null $response Optional response supplied by a listener.
     *
     * @return void
     */
    public function __construct(
        public Request $request,
        public RouteMatch $routeMatch,
        public ?Response $response = null,
    ) {}
}
