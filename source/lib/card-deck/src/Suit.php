<?php

/**
 * Cards
 *
 * Suits
 *
 * $Id$
 * $Date$
 *
 *  PHP version 8.5
 *
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  inanepain\card-deck
 * @category card-deck
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 *  _version_ $version
 */

declare(strict_types = 1);

namespace Inane\CardDeck;

use UnexpectedValueException;

/**
 * Enum: Suit
 *
 * Common suite order: CDHS
 */
enum Suit: string {
    /**
     * Suit: Clubs
     */
    case Clubs = 'c';
    /**
     * Suit: Diamonds
     */
    case Diamonds = 'd';
    /**
     * Suit: Hearts
     */
    case Hearts = 'h';
    /**
     * Suit: Spades
     */
    case Spades = 's';

    /**
     * Determines the colour of the card based on its suit.
     *
     * @return Colour The colour of the card, either black or red.
     *
     * @throws UnexpectedValueException If the card suit is not recognised.
     */
    public function colour(): Colour {
        return match ($this) {
            self::Spades, self::Clubs => Colour::Black,
            self::Hearts, self::Diamonds => Colour::Red,
        };
    }
}
