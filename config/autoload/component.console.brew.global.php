<?php

/**
 * Skeleton: Inane-FW
 *
 * Web or console application framework using the inanepain libraries.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package skeleton\inane-fw
 * @category inane-fw
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

return [
    'components' => [
        \Knot\Brew\Brew::class => [
            'ui' => [
                /**
                 * Flag icon to use for various statuses.
                 */
                'icon' => [
                    'flag' => '⚑', // ⛳️📌📍⚑⚐
                ],
            ],
            'info' => [
                /**
                 * Whether to display extended information about formulae automatically.
                 */
                'extended' => true,
            ],
            'review' => [
                /**
                 * Default Action to take when reviewing formulae.
                 * 'next' - Move to the next formula.
                 * 'hide' - Hide the current formula.
                 */
                'action' => 'next', // options: next, hide
                /**
                 * Whether to automatically update the review status of formulae when reviewing.
                 * - update if the last update ran longer than the value in seconds ago
                 * - 0 - off
                 */
                'autoupdate' => 60,
            ],
        ],
    ],
];
