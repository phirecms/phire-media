<?php

namespace Media\Form;

use Media\Table;
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

    /**
     * Set the field values
     *
     * @param  array $values
     * @return MediaLibrary
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && (null !== $this->name) && (null !== $this->folder)) {
            // Check for dupe name
            $library = Table\MediaLibraries::findBy(['name' => $this->name]);
            if (isset($library->id) && ($this->id != $library->id)) {
                $this->getElement('name')
                     ->addValidator(new Validator\NotEqual($this->name, 'That name already exists.'));
            }
            // Check for dupe name
            $library = Table\MediaLibraries::findBy(['folder' => $this->folder]);
            if (isset($library->id) && ($this->id != $library->id)) {
                $this->getElement('folder')
                     ->addValidator(new Validator\NotEqual($this->folder, 'That folder already exists.'));

                if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $this->folder)) {
                    $this->getElement('folder')
                         ->addValidator(new Validator\NotEqual($this->folder, 'That folder already exists on disk.'));
                }
            }
        }

        return $this;
    }

}