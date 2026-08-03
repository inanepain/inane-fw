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

declare(strict_types=1);

namespace Inane\CardDeck;

/**
 * Enum: Colour
 */
enum Colour: string {
    /**
     * Colour: Red
     */
    case Red = 'r';

    /**
     * Colour: Black
     */
    case Black = 'b';
}
