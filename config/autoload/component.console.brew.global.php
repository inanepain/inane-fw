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
                    'new' => '✷', // ✷✦✜
                    'url' => '☍', // ☍⎈
                ],
                /**
                 * Text colour options.
                 *
                 * colours: black, red, green, blue, yellow, purple, cyan, white
                 * styles: dim, bold, italic, underline, blink
                 */
                'text' => [
                    /**
                     * Action taken.
                     */
                    'action' => 'blue',
                    /**
                     * Package description.
                     */
                    'desc' => 'blue',
                    /**
                     * Webpage url.
                     */
                    'url' => 'underline green',
                    /**
                     * Progress counter.
                     */
                    'counter' => 'cyan',
                    /**
                     * Tag text.
                     */
                    'tag' => 'purple',
                    /**
                     * New indicator colour.
                     */
                    'icon' => 'purple',
                    /**
                     * Alert message.
                     */
                    'alert' => 'red',
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
                'action' => 'hide', // options: next, hide
                /**
                 * Whether to automatically update the review status of formulae when reviewing.
                 * - update if the last update ran longer than the value in seconds ago
                 * - 0 - off
                 */
                'autoupdate' => \Inane\Datetime\Unit\Hours::hours(1)->seconds->seconds,
            ],
            /**
             * Whether to perform a dry run without actually installing or updating formulae.
             */
            'dry-run' => false,
        ],
    ],
];
