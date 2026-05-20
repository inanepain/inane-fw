<?php

declare(strict_types=1);

use Inane\View\HtmlView;

// Create nested views
$header = new HtmlView('partials:header', ['title' => 'My App']);
$footer = new HtmlView('partials:footer', ['year' => date('Y')]);

$mainView = new HtmlView('layouts:layout');
$mainView
    ->nest('header', $header)
    ->setData('content', 'Welcome to the app')
    ->nest('footer', $footer);

echo $mainView->render();
