<?php

namespace Media\Form;

use Pop\File\Upload;
use Pop\Form\Form;
use Pop\Validator;

class Batch extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Batch
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'media-batch-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  array $settings
     * @return Batch
     */
    public function setFieldValues(array $values = null, array $settings = [])
    {
        parent::setFieldValues($values);
        return $this;
    }

}