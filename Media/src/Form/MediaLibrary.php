<?php

namespace Media\Form;

use Pop\Form\Form;
use Pop\Validator;

class MediaLibrary extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return MediaLibrary
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'media-library-form');
        $this->setIndent('    ');
    }

}