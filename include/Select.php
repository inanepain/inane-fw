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

use Inane\Cli\Cli;
use Inane\Console\Control\Select;
use Inane\Console\Control\SelectOption;
use Inane\Stdlib\Exception\ConfigurationException;

if (file_exists(getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    $autoload = getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
} elseif (!$autoload = getEnv('ENV_PHP_VENDOR')) {
    echo('No autoload.php file found at ' . getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' . PHP_EOL);
    echo('AND' . PHP_EOL);
    echo('Missing environment variable `ENV_PHP_VENDOR` which should point to the php vendor autoload.php file.' . PHP_EOL);
    exit(10);
}

require_once $autoload;

const INANE_DUMPER_HIDE_RUNKIT7 = true;
//\Inane\Dumper\Dumper::$showRunkit7SupportMessage = false;

$items = [
    new SelectOption(1, 'One (Names)', '{i}. {l}'),
    new SelectOption(2, 'Two (Places)', '{i}. {l}'),
    new SelectOption(3, 'Three (Yes/No)', '{i}. {l}'),
    new SelectOption(4, 'Four (Things)', '{i}. {l}'),
    new SelectOption(5, 'Five (Empty)'),
    new SelectOption(null, 'Exit', '{i}. {l}'),
];

$mainMenu = new Select(items: $items);
$option = $mainMenu(true);

if ($option === null) {
    $mainMenu->screen->beep('Exiting...');
    exit(0);
}

$menuItemFormat = null;

if ($option === 1) {
    $menuItemFormat = '{i}. {l}';
    $prompt = 'Select an option (Menu 1):';
    $items = [
        'Philip',
        'Nicole',
        'Philip & Nicole',
        'Neither',
    ];
} elseif ($option === 2) {
    $prompt = 'Select an option (Menu 2):';
    $items = [
        new SelectOption(1, 'Home', '{i}. {l}'),
        new SelectOption('w', 'Work', '{i}. {l}'),
        new SelectOption(null, 'None', '{i}. {l}'),
    ];
} elseif ($option === 3) {
    $menuItemFormat = '{l}';
    $prompt = 'Select an option (Menu 3):';
    $items = [
        new SelectOption(true, 'Yes'),
        new SelectOption(false, 'No'),
        new SelectOption(null, 'I don\'t know'),
    ];
} elseif ($option === 4) {
    $menuItemFormat = '{l}';
    $prompt = 'Select an option (Menu 4):';
    $items = [
        'plant'  => 'Avo',
        'fruit'  => 'Banana',
        'animal' => 'Cat',
        new SelectOption('animal', 'Dog'),
    ];
} elseif ($option === 5) {
    $prompt = 'Select an option (Menu 5):';
    $items = [];
}

try {
    $select = new Select(items: $items, prompt: $prompt, menuOptionFormat: $menuItemFormat);
    $selectedItem = $select();
    Cli::line((string)$selectedItem);

    // Menu 4 (Things)
    if ($option === 4 && $selectedItem->index === 'animal') {
        Cli::line('You selected an animal');
    }

    Cli::line(PHP_EOL);

    dd($select->screen === $mainMenu->screen, 'Shared Screen');
    dd(['option'        => $option,
        '$selectedItem' => $selectedItem,
    ]);
} catch (ConfigurationException $e) {
    Cli::line($e->getMessage());
}
