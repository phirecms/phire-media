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
     * @return Media
     */
    public function setFieldValues(array $values = null, array $settings = [])
    {
        parent::setFieldValues($values);

        if (($_POST) && ($_FILES) && (count($settings) == 4)) {
            $upload = new Upload(
                $settings['folder'], $settings['max_filesize'], $settings['disallowed_types'], $settings['allowed_types']
            );
            foreach ($_FILES as $file) {
                if (!empty($file['name'])) {
                    if (!$upload->test($file)) {
                        $this->getElement('error')
                             ->addValidator(new Validator\NotEqual('1', $upload->getErrorMessage() . ' (' . $file['name'] . ')'));
                    }
                }
            }
        }

        return $this;
    }

}