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

/**
 * Request-processing lifecycle event.
 */
final class RequestProcessingEvent extends StoppableEvent {
    /**
     * Constructor method.
     *
     * @param Request $request The request instance.
     *
     * @return void
     */
    public function __construct(public Request $request) {}
}
