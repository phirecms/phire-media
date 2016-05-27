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

use Phire\Media\Model\MediaLibrary;
use Pop\File\Upload;
use Pop\Form\Form;
use Pop\Validator;

/**
 * Media Form class
 *
 * @category   Phire\Media
 * @package    Phire\Media
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class Media extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Media
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'media-form');
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
            if (!$upload->test($_FILES['file'])) {
                $this->getElement('file')
                     ->addValidator(new Validator\NotEqual($this->file, $upload->getErrorMessage()));
            }
        }

        return $this;
    }

}