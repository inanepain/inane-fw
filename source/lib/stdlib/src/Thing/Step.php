<?php

/**
 * Inane: Stdlib
 *
 * Common classes, tools and utilities used throughout the inanepain libraries.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
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

/**
 * Step class for tracking progress towards a limit.
 *
 * @version 0.1.0
 */
class Step {
    /**
     * Current count.
     *
     * @var int
     */
    private int $count = 0;
    /**
     * Target limit.
     *
     * @var int
     */
    private int $limit = 1;
    /**
     * Checks if step can continue.
     *
     * @return bool false if count exceeds limit.
     */
    public bool $continue {
        get => !(++$this->count > $this->limit);
    }

    /**
     * Constructs a Step instance.
     *
     * @param int $target Target limit (min 1).
     */
    public function __construct(int $target = 1) {
        $this->limit = $target < 1 ? 1 : $target;
    }

    /**
     * Invokes continue check.
     *
     * @return bool Continue status.
     */
    public function __invoke(): bool {
        return $this->continue;
    }
}
