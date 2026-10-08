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
use Inane\Http\Response;
use Inane\Routing\RouteMatch;
use InvalidArgumentException;
use RuntimeException;

/**
 * Rendering lifecycle event.
 */
final class RenderingEvent extends StoppableEvent {
    /**
     * Constructor method to initialise the object with necessary dependencies.
     *
     * @param Request       $request    Instance of the Request class.
     * @param RouteMatch    $routeMatch Instance of the RouteMatch class.
     * @param Response|null $response   Optional instance of the Response class.
     *
     * @return void
     *
     * @throws InvalidArgumentException If any required parameter is invalid.
     * @throws RuntimeException If the initialisation process encounters issues.
     */
    public function __construct(
        public Request $request,
        public RouteMatch $routeMatch,
        public ?Response $response = null,
    ) {}
}
