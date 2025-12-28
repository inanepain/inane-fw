<?php

declare(strict_types=1);

use Inane\View\HtmlView;

// Simple page
$view = new HtmlView('dashboard', [
    'title' => 'User Dashboard',
    'widgets' => [
        ['title' => 'Stats', 'content' => '100 users'],
        ['title' => 'Revenue', 'content' => '$5,000']
    ],
    'sidebarItems' => ['Profile', 'Settings', 'Logout']
]);

$view->setLayout('main');
echo $view->render();

// Nested views
$cardView = new HtmlView('components:card', [
    'title' => 'Welcome',
    'content' => 'Hello user!'
]);

$pageView = new HtmlView('home');
$pageView
    ->setLayout('main')
    ->nest('welcomeCard', $cardView)
    ->setData('title', 'Home Page');

echo $pageView->render();