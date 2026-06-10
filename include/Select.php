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

use Inane\Console\Select;

if (file_exists(getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    $autoload = getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
} elseif (!$autoload = getEnv('ENV_PHP_VENDOR')) {
    echo ('No autoload.php file found at ' . getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' . PHP_EOL);
    echo ('AND' . PHP_EOL);
    echo ('Missing environment variable `ENV_PHP_VENDOR` which should point to the php vendor autoload.php file.' . PHP_EOL);
    exit(10);
}

require_once $autoload;

$items = [
    'Option One',
    'Aye',
    'OPT TWO',
    'Exit',
];

$select = new Select(items: $items);
$result = $select();
exit((string)$result);
