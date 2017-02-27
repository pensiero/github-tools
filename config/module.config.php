<?php
namespace GithubTools;

return [
    'controllers' => [
        'factories' => [
            __NAMESPACE__ . '\Controller\Console' => __NAMESPACE__ . '\ControllerFactory\ConsoleControllerFactory',
        ],
    ],
    'console'         => [
        'router' => [
            'routes' => [
                'github'           => [
                    'options' => [
                        'route'    => 'github <action>',
                        'defaults' => [
                            'controller' => __NAMESPACE__ . '\Controller\Console',
                        ]
                    ]
                ],
            ]
        ]
    ],
];