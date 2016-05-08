<?php

return [
    APP_URI => [
        '/media[/:lid]' => [
            'controller' => 'Phire\Media\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'index'
            ]
        ],
        '/media/add/:lid' => [
            'controller' => 'Phire\Media\Controller\IndexController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'add'
            ]
        ],
        '/media/batch/:lid' => [
            'controller' => 'Phire\Media\Controller\IndexController',
            'action'     => 'batch',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'batch'
            ]
        ],
        '/media/ajax/:lid' => [
            'controller' => 'Phire\Media\Controller\IndexController',
            'action'     => 'ajax',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'ajax'
            ]
        ],
        '/media/edit/:lid/:id' => [
            'controller' => 'Phire\Media\Controller\IndexController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'edit'
            ]
        ],
        '/media/remove/:lid' => [
            'controller' => 'Phire\Media\Controller\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'remove'
            ]
        ],
        '/media/browser[/:lid]' => [
            'controller' => 'Phire\Media\Controller\IndexController',
            'action'     => 'browser',
            'acl'        => [
                'resource'   => 'media',
                'permission' => 'browser'
            ]
        ],
        '/media/libraries[/]' => [
            'controller' => 'Phire\Media\Controller\LibraryController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'media-libraries',
                'permission' => 'index'
            ]
        ],
        '/media/libraries/add' => [
            'controller' => 'Phire\Media\Controller\LibraryController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'media-libraries',
                'permission' => 'add'
            ]
        ],
        '/media/libraries/edit/:id' => [
            'controller' => 'Phire\Media\Controller\LibraryController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'media-libraries',
                'permission' => 'edit'
            ]
        ],
        '/media/libraries/json/:id' => [
            'controller' => 'Phire\Media\Controller\LibraryController',
            'action'     => 'json',
            'acl'        => [
                'resource'   => 'media-libraries',
                'permission' => 'json'
            ]
        ],
        '/media/libraries/process' => [
            'controller' => 'Phire\Media\Controller\LibraryController',
            'action'     => 'process',
            'acl'        => [
                'resource'   => 'media-libraries',
                'permission' => 'process'
            ]
        ]
    ]
];
