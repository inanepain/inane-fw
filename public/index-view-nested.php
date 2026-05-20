<?php

declare(strict_types = 1);

use Inane\View\HtmlView;

// Nested views
$cardView = new HtmlView('components:card', [
    'title'   => 'Welcome',
    'content' => 'Hello user!',
]);
$pageView = new HtmlView('home');
$pageView
    ->setLayout('main')
    ->nest('welcomeCard', $cardView)
    ->setData('title', 'Home Page')
    ->setData('username', 'inanepain')
    ->setData('email', 'peep@inane.co.za')
;
echo $pageView->render();
