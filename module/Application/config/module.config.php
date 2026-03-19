<?php

/**
 *
 * @author Philip Michael Raab<philip@inane.co.za>
 *         vscode-fold=2
 */
declare(strict_types=1);

namespace Application;

return [
    'controllers' => [],
    'service_manager' => [],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view'
        ],
    ],
    'view_helper_config' => [
        'asset' => [
            'resource_map' => []
        ]
    ]
];
