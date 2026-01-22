<?php

declare(strict_types=1);

use Inane\Cli\Cli;
use Inane\Config\Config;
use Inane\Dumper\Dumper;
use Inane\Dumper\Type;
use Inane\Stdlib\Array\OptionsInterface;

require __DIR__ . '/../vendor/autoload.php';

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
    'DUMPER_SILENCE_CLASS' => DUMPER_SILENCE_CLASS,
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
        'E_NOTICE' => $debug_hide_notice,
        'E_WARNING' => $debug_hide_warning,
    ];
}

ini_set('error_reporting', (string)$error_reporting);
#endregion DEBUG HTACCESS FLAGS

(function (OptionsInterface $config): bool|int {
    Dumper::$enabled = $config->dumper->enabled;
    Dumper::$bufferOutput = $config->dumper->bufferOutput;

    Dumper::$showRunkit7SupportMessage = false;
    Dumper::$additionalTypes[] = Type::Todo;
    if (Cli::isCli()) {
        \Knot\Application::init($config)->run();
        return 0;
    } else {
        return true;
    }
})(Config::fromConfigFile());
