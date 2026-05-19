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

use Inane\Datetime\Timespan;
use Inane\Datetime\Timestamp;

/**
 * Seconds
 * 
 * @version 0.1.0
 */
class Seconds extends AbstractUnit {
    public Timestamp $timestamp {
        get => new Timestamp($this->unit);
    }
    public Timespan $timespan {
        get => new Timespan($this->unit);
    }

    public Minutes $minutes {
        get => new Minutes($this->unit / 60);
    }

    public int $milliseconds {
        get => $this->unit * 1000;
    }
    public int $nanoseconds {
        get => $this->milliseconds * 1000;
    }
    public function __construct(public int $seconds = 0) {
    }

    public static function seconds(int $seconds): static {
        return new static($seconds);
    }
}
