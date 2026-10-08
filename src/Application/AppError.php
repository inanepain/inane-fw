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

declare(strict_types = 1);

namespace Knot\Application;

use Inane\Stdlib\Enum\CoreEnumInterface;
use Inane\Stdlib\Enum\CoreEnumTrait;

/**
 * AppError
 *
 * @version 1.0.0
 */
enum AppError: int implements CoreEnumInterface {
    /**
     * Represents an invalid route error with an associated HTTP status code of 400.
     */
    case InvalidRoute = 400;

    use CoreEnumTrait;

    /**
     * Get the user-friendly message
     *
     * @return string Error Message
     */
    public function message(): string {
        return match ($this) {
            self::InvalidRoute => 'Invalid Route: not found.',
        };
    }
}
