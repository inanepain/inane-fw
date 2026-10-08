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
use Inane\Http\Request;
use Inane\Routing\RouteMatch;
use InvalidArgumentException;

/**
 * Routing lifecycle event.
 */
final class RoutingEvent extends StoppableEvent {
    /**
     * Constructor method.
     *
     * @param Request         $request    The incoming request instance.
     * @param RouteMatch|null $routeMatch Optional route match instance.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the provided arguments are invalid.
     */
    public function __construct(
        public Request $request,
        public ?RouteMatch $routeMatch = null,
    ) {}
}
