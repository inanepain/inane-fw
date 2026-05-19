<?php

/**
 * Inane: Stdlib
 *
 * Common classes that cover a wide range of cases that are used throughout the inanepain libraries.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\stdlib
 * @category stdlib
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\Stdlib\Thing;

use const false;
use const true;

/**
 * Toggle class for state switching.
 *
 * @version 0.1.0
 */
class Toggle {
    /**
     * Current state.
     *
     * @var bool
     */
    private bool $state = false;

    /**
     * Toggles state.
     *
     * @return bool New state.
     */
    public bool $toggle {
        get => $this->state = !$this->state;
    }

    /**
     * Value based on toggle.
     *
     * @return null|bool|string|int|float|object
     */
    public null|bool|string|int|float|object $toggleValue {
        get => $this->toggle ? $this->true : $this->false;
    }

    /**
     * Uses value or toggle.
     *
     * @return null|bool|string|int|float|object
     */
    public null|bool|string|int|float|object $toggleUse {
        get => $this->useValue ? $this->toggleValue : $this->toggle;
    }

    /**
     * Constructs a Toggle instance.
     *
     * @param bool $initialState Initial state (inverted).
     * @param bool $useValue Use values flag.
     * @param null|bool|string|int|float|object $true True value.
     * @param null|bool|string|int|float|object $false False value.
     */
    public function __construct(bool $initialState = true, private bool $useValue = false, private null|bool|string|int|float|object $true = true, private null|bool|string|int|float|object $false = false) {
        $this->state = !$initialState;
    }

    /**
     * Invokes toggle logic.
     *
     * @param ?bool $getValue Mode selector.
     * @return null|bool|string|int|float|object Result.
     */
    public function __invoke(?bool $getValue = null): null|bool|string|int|float|object {
        return match ($getValue) {
            true => $this->toggleValue,
            false => $this->toggle,
            null => $this->toggleUse,
        };
    }
}
