<?php

/**
 * Select
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\select
 * @category select
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

/**
 * Screen utility class for console operations.
 */

namespace Inane\Console;

use Stringable;

class SelectOption implements Stringable {
    public function __construct(
        protected(set) int|string|float $index,
        protected(set) string $label,
    ) {

    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->label;
    }
}
