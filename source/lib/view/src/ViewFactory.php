<?php

/**
 * Inane: View
 *
 * View layer with models for the most common content types.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\view
 * @category view
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\View;

use Inane\Stdlib\Exception\InvalidArgumentException;

/**
 * A factory class for creating instances of various view types.
 */
class ViewFactory {
    /**
     * Creates a new instance of a view based on the specified format.
     *
     * @param string $format  The format of the view to create ('html', 'json', 'text').
     * @param mixed  ...$args Additional arguments required for the view creation.
     *
     * @return View The created view instance corresponding to the specified format.
     *
     * @throws InvalidArgumentException If the specified format is not recognised.
     */
    public static function create(string $format, ...$args): View {
        return match($format) {
            'html' => new HtmlView(...$args),
            'json' => new JsonView(...$args),
            'text' => new TextView(...$args),
            default => throw new InvalidArgumentException("Unknown format: {$format}")
        };
    }
}