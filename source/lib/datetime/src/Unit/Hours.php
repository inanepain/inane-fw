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
 * Hours
 * 
 * @version 0.1.0
 */
class Hours extends AbstractUnit {
    public Days $days {
        get => new Days($this->unit / 24);
    }

    public Minutes $minutes {
        get => Minutes::minutes($this->unit * 60);
    }

    public Seconds $seconds {
        get => $this->type->to($this, UnitEnum::Seconds);
    }
    
    public function __construct(public int|float $hours = 0) {
    }

    public static function hours(int|float $hours): static {
        return new static($hours);
    }
}
