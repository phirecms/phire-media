<?php

return [
    APP_URI => [
        '/media[/:lid]' => [
            'controller' => 'Media\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'index'
            ]
        ],
        '/media/add' => [
            'controller' => 'Media\Controller\IndexController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'add'
            ]
        ],
        '/media/edit/:id' => [
            'controller' => 'Media\Controller\IndexController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'edit'
            ]
        ],
        '/media/remove' => [
            'controller' => 'Media\Controller\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'remove'
            ]
        ],
        '/media/libraries[/]' => [
            'controller' => 'Media\Controller\LibraryController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'media-libraries',
                'permission' => 'index'
            ]
        ],
        '/media/libraries/add' => [
            'controller' => 'Media\Controller\LibraryController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'media-libraries',
                'permission' => 'add'
            ]
        ],
        '/media/libraries/edit/:id' => [
            'controller' => 'Media\Controller\LibraryController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'media-libraries',
                'permission' => 'edit'
            ]
        ],
        '/media/libraries/remove' => [
            'controller' => 'Media\Controller\LibraryController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'media-libraries',
                'permission' => 'remove'
            ]
        ]
    ]
];
