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

//$page = 1;
//if ($page === 1) {
//    // Simple page
//    $view = new HtmlView('dashboard', [
//        'title'        => 'User Dashboard',
//        'widgets'      => [
//            [
//                'title'   => 'Stats',
//                'content' => '100 users'
//            ],
//            [
//                'title'   => 'Revenue',
//                'content' => '$5,000'
//            ]
//        ],
//        'sidebarItems' => [
//            'Profile',
//            'Settings',
//            'Logout'
//        ]
//    ]);
//
//    $view->setLayout('main');
//    echo $view->render();
//} elseif ($page === 2) {
//    // Nested views
//    $cardView = new HtmlView('components:card', [
//        'title'   => 'Welcome',
//        'content' => 'Hello user!'
//    ]);
//
//    $pageView = new HtmlView('home');
//    $pageView->setLayout('main')
//        ->nest('welcomeCard', $cardView)
//        ->setData('title', 'Home Page')
//    ;
//
//    echo $pageView->render();
//} elseif ($page === 3) {
//    $apiView = new JsonView([
//        'status' => 'success',
//        'data' => [
//            'user' => ['id' => 1, 'name' => 'John']
//        ]
//    ]);
//
//    header('Content-Type: application/json');
//    echo $apiView->render();
//}
//exit;

//require_once 'index-merge.php';
//require_once 'index-output.php';
require_once 'index-activitypicker.php';
exit;

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

echo "Return Code: $returnCode" . PHP_EOL;
echo "Error reporting: $error_reporting" . PHP_EOL;
