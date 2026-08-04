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
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  skeleton\inane-fw
 * @category inane-fw
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

use Inane\Console\Router\ConsoleRouter;

return [
    'components' => [
        ConsoleRouter::class => [
            'commands'  => [
                'glob'        => 'src/*/*Commands.php',
                'glob_ignore' => '/(Abstract)/',
                'default'     => [],
            ],
        ],
    ],
];
