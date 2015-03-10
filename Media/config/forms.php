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
            'library_id' => [
                'type'  => 'hidden',
                'value' => 0
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
            'adapter' => [
                'type'     => 'select',
                'label'    => 'Image Adapter',
                'required' => true,
                'value'    => [
                    '----' => '----'
                ],
                'validators' => new \Pop\Validator\NotEqual('----', 'You must select an adapter.')
            ],
            'max_filesize' => [
                'type'       => 'text',
                'label'      => 'Max Filesize',
                'attributes' => ['size' => 10]
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
                'attributes' => [
                    'size'    => 60,
                    'style'   => 'width: 99.5%',
                    'onkeyup' => "phire.createSlug(this.value, '#folder');"
                ]
            ],
            'folder' => [
                'type'       => 'text',
                'label'      => 'Folder',
                'required'   => true,
                'attributes' => [
                    'size'  => 60,
                    'style' => 'width: 99.5%'
                ]
            ],
            'allowed_types' => [
                'type'  => 'textarea',
                'label' => 'Allowed Types (Comma-separated list of file extensions)<span class="allowed-types-span">[<a href="#" onclick="phire.setDefaultAllowedTypes(); return false;">Set Defaults</a> ]</span>',
                'attributes' => [
                    'cols'  => 40,
                    'rows'  => 3,
                    'style' => 'width: 100%'
                ]
            ],
            'disallowed_types' => [
                'type'  => 'textarea',
                'label' => 'Disallowed Types (Comma-separated list of file extensions)<span class="allowed-types-span">[<a href="#" onclick="phire.setDefaultDisallowedTypes(); return false;">Set Defaults</a> ]</span>',
                'attributes' => [
                    'cols'  => 40,
                    'rows'  => 3,
                    'style' => 'width: 100%'
                ]
            ]
        ],
        [
            'action_name_1' => [
                'type'       => 'text',
                'label'      => '<a href="#" onclick="return phire.addMediaActions();">[+]</a> Actions',
                'attributes' => [
                    'placeholder' => 'Name',
                    'size'        => 20
                ]
            ],
            'action_method_1' => [
                'type'  => 'select',
                'value' => [
                    '----'           => '[ Method ]',
                    'resize'         => 'resize',
                    'resizeToWidth'  => 'resizeToWidth',
                    'resizeToHeight' => 'resizeToHeight',
                    'scale'          => 'scale',
                    'crop'           => 'crop',
                    'cropThumb'      => 'cropThumb'
                ]
            ],
            'action_params_1' => [
                'type'       => 'text',
                'attributes' => [
                    'placeholder' => 'Parameters',
                    'size'        => 20
                ]
            ],
            'action_quality_1' => [
                'type'       => 'text',
                'attributes' => [
                    'placeholder' => 'Quality',
                    'size'        => 7
                ]
            ],
        ]
    ]
];
