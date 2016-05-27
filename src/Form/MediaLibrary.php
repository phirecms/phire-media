<?php
/**
 * Phire Media Module
 *
 * @link       https://github.com/phirecms/phire-media
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Media\Form;

use Phire\Media\Table;
use Pop\Form\Form;
use Pop\Validator;

/**
 * Media Library Form class
 *
 * @category   Phire\Media
 * @package    Phire\Media
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
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