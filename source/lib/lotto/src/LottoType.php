<?php

/**
 * Inane: Lotto
 *
 * Lotto.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\lotto
 * @category lotto
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\Lotto;

use Inane\Stdlib\Enum\CoreEnumInterface;
use Inane\Stdlib\Enum\CoreEnumTrait;

/**
 * LottoType
 *
 * The LottoType enum represents different types of lotto games.
 * It provides specific attributes and methods relevant to each lotto type.
 *
 * This enum implements the CoreEnumInterface and utilizes CoreEnumTrait
 * for added functionality and consistency with enum operations.
 *
 * @version 0.1.0
 */
enum LottoType implements CoreEnumInterface {
    /**
     * Standard Lotto Draw
     */
    case Lotto;
    /**
     * PowerBall Lottery Draw
     */
    case PowerBall;

    /**
     * Trait CoreEnumTrait
     *
     * A trait designed to provide utility methods for working with enum-like constructs in PHP.
     * This trait typically includes helper methods that are common when working with enumerations,
     * such as retrieving all possible values or keys.
     *
     * It is intended to be used in conjunction with classes or interfaces that define
     * enumerable constants or similar structures requiring such utility methods.
     */
    use CoreEnumTrait;

    /**
     * Get the days for the lottery type.
     *
     * @return string[] Array of days for the lottery type.
     */
    public function days(): array {
        return match ($this) {
            static::Lotto => ['Wednesday', 'Saturday'],
            static::PowerBall => ['Tuesday', 'Friday'],
        };
    }
}