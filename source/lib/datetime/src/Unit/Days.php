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
 * Days
 * 
 * @version 0.1.0
 */
class Days extends AbstractUnit {
    public Weeks $weeks {
        get => new Weeks($this->unit / 7);
    }

    public Hours $hours {
        get => Hours::hours($this->unit * 24);
    }
    
    public function __construct(public int|float $days = 0) {
    }

    public static function days(int|float $days): static {
        return new static($days);
    }
}
