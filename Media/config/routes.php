<?php

return [
    APP_URI => [
        '/media[/]' => [
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
        '/media/library[/]' => [
            'controller' => 'Media\Controller\LibraryController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'media-library',
                'permission' => 'index'
            ]
        ],
        '/media/library/add' => [
            'controller' => 'Media\Controller\LibraryController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'media-library',
                'permission' => 'add'
            ]
        ],
        '/media/library/edit/:id' => [
            'controller' => 'Media\Controller\LibraryController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'media-library',
                'permission' => 'edit'
            ]
        ],
        '/media/library/remove' => [
            'controller' => 'Media\Controller\LibraryController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'media-library',
                'permission' => 'remove'
            ]
        ]
    ]
];
