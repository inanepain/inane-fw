<?php

declare(strict_types = 1);

use Inane\Cli\Cli;
use Inane\Dumper\Dumper;
use Inane\Dumper\Type;
use Knot\Application;

chdir(dirname(__DIR__));

require_once 'vendor/autoload.php';
require_once '/Users/philip/Developer/php/playground/dirty-loader/src/Dirtyloader.php';

Dirtyloader::register([
    'loaders' => [
        'path',
    ],
    'path' => [
        'include',
    ],
]);

#region DEBUG HTACCESS FLAGS
// TODO: MAJOR WORK ON THIS DEBUG STUFF
/**
 * Define global debug constants: START
 */
$debug_options = [];

// debug
define('APP_DEBUG', (bool)\getenv('DEVELOPMENT'));
$debug_options['APP_DEBUG'] = \APP_DEBUG;

$debug_hide_deprecation = (bool)getenv('DEBUG_HIDE_DEPRECATION');
$debug_hide_notice = (bool)getenv('DEBUG_HIDE_NOTICE');
$debug_hide_warning = (bool)getenv('DEBUG_HIDE_WARNING');

// Dumper options: random
// define('DUMPER_SILENCE_CLASS', (random_int(0, 1) == 0));
define('DUMPER_SILENCE_CLASS', (bool)getenv('DUMPER_SILENCE_CLASS'));
// define('DUMPER_SILENCE_METHOD', (random_int(0, 1) == 0));
define('DUMPER_SILENCE_METHOD', (bool)getenv('DUMPER_SILENCE_METHOD'));

$debug_options['DUMPER'] = [
    'DUMPER_SILENCE_CLASS'  => DUMPER_SILENCE_CLASS,
    'DUMPER_SILENCE_METHOD' => DUMPER_SILENCE_METHOD,
];

// error reporting default
$error_reporting = APP_DEBUG ? E_ALL : E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED;

if (APP_DEBUG || Cli::isCli()) {
    if ($debug_hide_deprecation) $error_reporting &= ~E_DEPRECATED;
    if ($debug_hide_notice) $error_reporting &= ~E_NOTICE;
    if ($debug_hide_warning) $error_reporting &= ~E_WARNING;

    $debug_options['HIDE'] = [
        'E_DEPRECATION' => $debug_hide_deprecation,
        'E_NOTICE'      => $debug_hide_notice,
        'E_WARNING'     => $debug_hide_warning,
    ];
}

//echo "Error reporting: $error_reporting" . PHP_EOL;
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

//ini_set('error_reporting', (string)$error_reporting);
#endregion DEBUG HTACCESS FLAGS

$includes = [
    null, // 0
    'merge', // 1
    'output', // 2
    'view-json', // 3
    'view-nested', // 4
    'view-nested2', // 5 error: startBlock
    'view-simple', // 6
    'html-builder', // 7
];

$include = $includes[0];

if ($include !== null) {
    require_once "index-{$include}.php";
    exit;
}

$returnCode = (static function(Application $app): bool|int {
    // FIX: boo
    // FIXME: boo
    // BUG: bug
    // DEBUG: example debug
    // [ ]: something not done.
    // [x]: this has been completed!
    // TODO: PhpRenderer example
    // HACK: fixed with spit and snot
    // NOTE: boo

    Dumper::$showRunkit7SupportMessage = false;
    Dumper::$additionalTypes[] = Type::Todo;
    if (Cli::isCli()) {
        return $app->run();
    }

    return true;
})(Application::app());

//echo "Return Code: $returnCode" . PHP_EOL;
//echo "Error reporting: $error_reporting" . PHP_EOL;
