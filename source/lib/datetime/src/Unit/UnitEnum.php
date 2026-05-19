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

use Inane\Stdlib\Enum\CoreEnumInterface;
use Inane\Stdlib\Enum\CoreEnumTrait;

/**
 * Unit
 * 
 * @version 0.1.0
 */
enum UnitEnum: int implements CoreEnumInterface {
    case Seconds = 1;
    case Minutes = 60;
    case Hours = 3600;
    case Days = 86400;
    case Weeks = 604800;

    use CoreEnumTrait;

    public function to(UnitInterface $unit, UnitEnum $type): UnitInterface {
        $value = ($unit->type->value * $unit->unit) / $type->value;
        return new ('\\Inane\\Datetime\\Unit\\' . $type->name)($value);
    }
}
