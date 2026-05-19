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

/**
 * Weeks
 * 
 * @version 0.1.0
 */
class Weeks extends AbstractUnit {
    public Days $days {
        get => Days::days($this->unit * 7);
    }

    public Seconds $seconds {
        get => $this->type->to($this, UnitEnum::Seconds);
    }
    
    public function __construct(public int|float $weeks = 0) {
    }

    public static function weeks(int|float $weeks): static {
        return new static($weeks);
    }
}
