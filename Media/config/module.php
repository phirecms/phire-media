<?php
/**
 * Module Name: Media
 * Author: Nick Sagona
 * Description: This is the media module for Phire CMS 2
 * Version: 1.0
 */
return [
    'Media' => [
        'prefix'     => 'Media\\',
        'src'        => __DIR__ . '/../src',
        'routes'     => include 'routes.php',
        'resources'  => include 'resources.php',
        'forms'      => include 'forms.php',
        'nav.phire'  => [
            'media' => [
                'name' => 'Media',
                'href' => '/media',
                'acl' => [
                    'resource'   => 'media',
                    'permission' => 'index'
                ],
                'attributes' => [
                    'class' => 'media-nav-icon'
                ]
            ]
        ],
        'nav.module' => [
            'name' => 'Media Libraries',
            'href' => '/media/libraries',
            'acl'  => [
                'resource'   => 'media-libraries',
                'permission' => 'index'
            ]
        ],
        'models' => [
            'Media\Model\Media' => []
        ],
        'events' => [
            [
                'name'     => 'app.route.pre',
                'action'   => 'Media\Model\Media::addModels',
                'priority' => 1000
            ]
        ]
    ]
];
