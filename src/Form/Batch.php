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

use Pop\File\Upload;
use Pop\Form\Form;
use Pop\Validator;

/**
 * Batch Form class
 *
 * @category   Phire\Media
 * @package    Phire\Media
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
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