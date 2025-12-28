<?php

/**
 * Inane: Datetime
 *
 * Inane Datetime Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\datetime
 * @category datetime
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\Datetime\Unit;

/**
 * UnitInterface
 * 
 * @version 0.1.0
 */
interface UnitInterface {
    /**
     * The unit type.
     */
    public UnitEnum $type {get;}

    /**
     * The unit value.
     */
    public int|float $unit {get;}

    /**
     * Converts the object to its string representation.
     *
     * @return string The string representation of the object.
     */
    public function __toString(): string;
}