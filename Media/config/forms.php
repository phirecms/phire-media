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
            'max_filesize' => [
                'type'       => 'text',
                'label'      => 'Max Filesize',
                'attributes' => ['size' => 10]
            ],
            'adapter' => [
                'type'  => 'select',
                'label' => 'Image Adapter',
                'value' => [
                    '----' => '----'
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
                'type'  => 'checkbox',
                'label' => 'Allowed Types <span id="allowed-types-span">[<a href="#" onclick="phire.checkAllAllowedTypes(); return false;">Check All</a> | <a href="#" onclick="phire.uncheckAllAllowedTypes(); return false;">Uncheck All</a> | <a href="#" onclick="phire.invertAllowedTypes(); return false;">Inverse</a>]<br /><em>(Uncheck all to allow any type.)</em></span>',
                'value' => [
                    'ai'   => 'ai',   'aif'  => 'aif',  'aiff'  => 'aiff',  'avi'   => 'avi',   'bmp' => 'bmp', 'bz2'  => 'bz2',
                    'css'  => 'css',  'csv'  => 'csv',  'doc'   => 'doc',   'docx'  => 'docx',  'eps' => 'eps', 'fla'  => 'fla',
                    'flv'  => 'flv',  'gif'  => 'gif',  'gz'    => 'gz',    'html'  => 'html',  'htm' => 'htm', 'jpe'  => 'jpe',
                    'jpg'  => 'jpg',  'jpeg' => 'jpeg', 'js'    => 'js',    'json'  => 'json',  'mov' => 'mov', 'mp2'  => 'mp2',
                    'mp3'  => 'mp3',  'mp4'  => 'mp4',  'mpg'   => 'mpg',   'mpeg'  => 'mpeg',  'otf' => 'otf', 'pdf'  => 'pdf',
                    'phar' => 'phar', 'php'  => 'php',  'phtml' => 'phtml', 'png' => 'png', 'ppt'  => 'ppt', 'pptx' => 'pptx',
                    'psd'  => 'psd',  'rar'   => 'rar',   'sql'   => 'sql',   'svg' => 'svg', 'swf'  => 'swf', 'tar'  => 'tar',
                    'tbz'  => 'tbz',  'tbz2'  => 'tbz2',  'tgz'   => 'tgz',   'tif' => 'tif', 'tiff' => 'tiff', 'tsv'  => 'tsv',
                    'ttf'  => 'ttf',  'txt'   => 'txt',   'wav'   => 'wav',   'wma' => 'wma', 'wmv'  => 'wmv', 'xls'  => 'xls',
                    'xlsx' => 'xlsx', 'xml'   => 'xml',   'yml' => 'yml', 'zip'  => 'zip'
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
                    '----'           => '[ Select Method ]',
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
                    'size'        => 10
                ]
            ],
        ]
    ]
];
