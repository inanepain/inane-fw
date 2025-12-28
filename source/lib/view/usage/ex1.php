<?php

declare(strict_types=1);

use Inane\View\HtmlView;
use Inane\View\JsonView;

// Create nested views
$header = new HtmlView('header', ['title' => 'My App']);
$footer = new HtmlView('footer', ['year' => date('Y')]);

$mainView = new HtmlView('layout');
$mainView
    ->nest('header', $header)
    ->setData('content', 'Welcome to the app')
    ->nest('footer', $footer);

echo $mainView->render();

// Or for JSON API
$apiView = new JsonView([
    'status' => 'success',
    'data' => [
        'user' => ['id' => 1, 'name' => 'John']
    ]
]);

header('Content-Type: application/json');
echo $apiView->render();