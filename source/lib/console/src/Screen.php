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

/**
 * Screen utility class for terminal-based operations.
 */
class Screen {
    /**
     * Clears the console screen by emitting the appropriate escape sequence.
     *
     * @return void
     */
    public function clear(): void {
        echo "\033[H\033[J";
    }

    /**
     * Restores the terminal to a sane state by executing the 'stty sane' command.
     *
     * @return void
     *
     * @throws \RuntimeException If the system command fails to execute.
     */
    public function escape(): void {
        system('stty sane');
    }

    /**
     * Disables canonical mode and terminal echo by executing the 'stty -icanon -echo' command.
     *
     * @return void
     *
     * @throws \RuntimeException If the system command fails to execute.
     */
    public function escapeANSI(): void {
        system('stty -icanon -echo');
    }
}
