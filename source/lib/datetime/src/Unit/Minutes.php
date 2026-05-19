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
 * Minutes
 * 
 * @version 0.1.0
 */
class Minutes extends AbstractUnit {
    public Hours $hours {
        get => new Hours($this->unit / 60);
    }

    public Seconds $seconds {
        get => Seconds::seconds($this->unit * 60);
    }
    
    public function __construct(public int|float $minutes = 0) {
    }

    public static function minutes(int|float $minutes): static {
        return new static($minutes);
    }
}
