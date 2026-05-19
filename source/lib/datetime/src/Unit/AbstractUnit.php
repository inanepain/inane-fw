<?php

/**
 * Inane: Datetime
 *
 * Inane Datetime Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
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

use Stringable;

use function array_pop;
use function explode;
use function strtolower;

/**
 * AbstractUnit
 *
 * @version 0.1.0
 */
abstract class AbstractUnit implements UnitInterface, Stringable {
    public UnitEnum $type {
        get => UnitEnum::{@array_pop(@explode('\\', static::class))};
    }

    public int|float $unit {
        get => $this->{strtolower(@array_pop(@explode('\\', static::class)))};
    }

    public function __toString(): string {
        return (string)$this->unit;
    }
}
