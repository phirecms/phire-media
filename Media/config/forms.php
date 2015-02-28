<?php

return [
    'Media\Form\Media' => [
        [
            'submit' => [
                'type'       => 'submit',
                'value'      => 'Save',
                'attributes' => [
                    'class'  => 'save-btn wide'
                ]
            ],
            'id' => [
                'type'  => 'hidden',
                'value' => 0
            ]
        ],
        [
            'title' => [
                'type'       => 'text',
                'label'      => 'Title',
                'attributes' => ['size' => 60]
            ],
            'file' => [
                'type'       => 'file',
                'label'      => 'File',
                'attributes' => ['size' => 60]
            ]
        ]
    ],
    'Media\Form\MediaLibrary' => [
        [
            'submit' => [
                'type'       => 'submit',
                'value'      => 'Save',
                'attributes' => [
                    'class'  => 'save-btn wide'
                ]
            ],
            'order' => [
                'type'       => 'text',
                'label'      => 'Order',
                'attributes' => ['size' => 3],
                'value'      => 0
            ],
            'id' => [
                'type'  => 'hidden',
                'value' => 0
            ]
        ],
        [
            'name' => [
                'type'       => 'text',
                'label'      => 'Name',
                'required'   => true,
                'attributes' => ['size' => 60]
            ]
        ]
    ]
];
