<?php

/**
 * inane-fw
 *
 * Inane Framework
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\PROJECT
 * @category PROJECT
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 *
 */

declare(strict_types = 1);

use Inane\Config\Config;
use Inane\Dumper\{
    Dumper,
    Type};
use Inane\Rebecca\Rebecca;
use Inane\Stdlib\Array\OptionsInterface;
use Knot\WebSocket\BroadcastCommand;
use Knot\WebSocket\EchoCommand;
use Knot\WebSocket\InfoCommand;
use Knot\WebSocket\PingCommand;
use Knot\WebSocket\SetRankCommand;

// Set working directory to project root for consistent relative paths
chdir(dirname(__DIR__));

if (file_exists('vendor/autoload.php')) {
    $autoload = 'vendor/autoload.php';
} elseif (!$autoload = getEnv('ENV_PHP_VENDOR')) {
    echo('No autoload.php file found at ' . getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' . PHP_EOL);
    echo('AND' . PHP_EOL);
    echo('Missing environment variable `ENV_PHP_VENDOR` which should point to the php vendor autoload.php file.' . PHP_EOL);
    exit(10);
}

require_once $autoload;

// echo "Autoload: $autoload";

// phpinfo(); exit;

//require_once 'wip/dumper-hide-runkit7.php';
Dumper::$additionalTypes[] = Type::Todo;

(static function(OptionsInterface $config): bool {
    // BUG: bug
    // DEBUG: example debug
    // FIX: boo
    // FIXME: boo
    // HACK: hack
    // NOTE: boo
    // TODO: PhpRenderer example
    // [ ]: boo
    // [x]: boo

    // Initialise and start the server
    $wsServer = Rebecca::init($config);

    // Register commands with different rank requirements
    $wsServer->registerCommand(new PingCommand());        // Rank 0 - Everyone
    $wsServer->registerCommand(new EchoCommand());        // Rank 0 - Everyone
    $wsServer->registerCommand(new InfoCommand());        // Rank 0 - Everyone
    $wsServer->registerCommand(new BroadcastCommand());   // Rank 5 - Moderators
    $wsServer->registerCommand(new SetRankCommand());     // Rank 10 - Admins

    // Start the server
    $wsServer->start();

    \Inane\Dumper\Dumper::$enabled = true;
    \Inane\Dumper\Dumper::$bufferOutput = false;
    //    dd($config);
    //    Rebecca::init($config)->run();

    return true;
})(Config::fromConfigFile());
