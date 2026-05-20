<?php

declare(strict_types = 1);

use Inane\View\JsonView;

$apiView = new JsonView([
    'status' => 'success',
    'data'   => [
        'user' => [
            'id'   => 1,
            'name' => 'John',
        ],
    ],
]);

@header('Content-Type: application/json');
echo $apiView->render();
