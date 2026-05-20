<?php

/**
 * doc
 *
 * Description: doc
 *
 * PHP version 8.1
 *
 * @version $Id$
 * $Date$
 * @license UNLICENSE doc
 * @license https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
 *
 * @author  Philip Michael Raab<peep@inane.co.za>
 *
 */

declare(strict_types = 1);

namespace Inane\View\Helper;

use Inane\Stdlib\Enum\CoreEnumInterface;

use function strcasecmp;

/**
 * Action
 *
 * @version 0.1.0
 */
enum Action: string implements CoreEnumInterface {
    case Start = 'start';
    case End   = 'end';
    case Close = 'close';
    case None  = '';

    /**
     * Try get enum from name
     *
     * note: interface does not have $ignoreCase.
     *  This is an extension and might not be wanted everywhere.
     *
     * @param string $name
     * @param bool   $ignoreCase case insensitive option
     *
     * @return null|static
     */
    public static function tryFromName(string $name, bool $ignoreCase = false): ?static {
        foreach(static::cases() as $case)
            if (($ignoreCase && strcasecmp($case->name, $name) == 0) || $case->name === $name)
                return $case;

        return null;
    }
}
