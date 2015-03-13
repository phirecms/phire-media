<?php

namespace Media\Model;

use Media\Table;
use Phire\Model\AbstractModel;
use Pop\File\Upload;

class Media extends AbstractModel
{

    /**
     * Get all libraries
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $order = $this->getSortOrder($sort, $page);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $rows = Table\Media::findBy(['library_id' => $this->lid], null, [
                'offset' => $page,
                'limit'  => $limit,
                'order'  => $order
            ])->rows();
        } else {
            $rows = Table\Media::findBy(['library_id' => $this->lid], null, [
                'order'  => $order
            ])->rows();
        }

        $library = new MediaLibrary();
        $library->getById($this->lid);

        foreach ($rows as $key => $value) {
            $value->icon = $this->getFileIcon($value->file, $library);
        }

        return $rows;
    }

    /**
     * Get media by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $media = Table\Media::findById($id);
        if (isset($media->id)) {
            $data = $media->getColumns();

            $library = new MediaLibrary();
            $library->getById($data['library_id']);

            $data['icon'] = $this->getFileIcon($data['file'], $library);

            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Save new media
     *
     * @param  array $file
     * @param  array $fields
     * @return void
     */
    public function save(array $file, array $fields)
    {
        $library = new MediaLibrary();
        $library->getById($fields['library_id']);

        $folder   = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
        $fileName = (new Upload($folder))->upload($file);

        if (empty($fields['title'])) {
            $title = ucwords(str_replace(['_', '-'], [' ', ' '], substr($fileName, 0, strrpos($fileName, '.'))));
        } else {
            $title = $fields['title'];
        }

        $media = new Table\Media([
            'library_id' => $fields['library_id'],
            'title'      => $title,
            'file'       => $fileName
        ]);
        $media->save();

        if (null !== $library->adapter) {
            $class     = 'Pop\Image\\' .  $library->adapter;
            $formats   = array_keys($class::getFormats());
            $fileParts = pathinfo($fileName);
            if (!empty($fileParts['extension']) && in_array($fileParts['extension'], $formats)) {
                $this->processImage($fileName, $library);
            }
        }

        $this->data = array_merge($this->data, $media->getColumns());
    }

    /**
     * Save batch media
     *
     * @param  array $files
     * @param  array $fields
     * @return void
     */
    public function batch(array $files, array $fields)
    {
        foreach ($files as $file) {
            if (!empty($file['name'])) {
                $this->save($file, $fields);
            }
        }
    }

    /**
     * Update an existing media
     *
     * @param  array $file
     * @param  array $fields
     * @return void
     */
    public function update(array $file = null, array $fields)
    {
        $media = Table\Media::findById($fields['id']);
        if (isset($media->id)) {
            if ((null !== $file) && !empty($file['name'])) {
                $library = new MediaLibrary();
                $library->getById($fields['library_id']);

                $folder = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
                if (file_exists($folder . DIRECTORY_SEPARATOR . $fields['current_file']) &&
                    !is_dir($folder . DIRECTORY_SEPARATOR . $fields['current_file'])) {
                    unlink($folder . DIRECTORY_SEPARATOR . $fields['current_file']);
                }
                $fileName = (new Upload($folder))->upload($file);
            } else {
                $fileName = $fields['current_file'];
            }

            if (empty($fields['title'])) {
                $title = ucwords(str_replace(['_', '-'], [' ', ' '], substr($fileName, 0, strrpos($fileName, '.'))));
            } else {
                $title = $fields['title'];
            }

            $media->library_id = $fields['library_id'];
            $media->title      = $title;
            $media->file       = $fileName;
            $media->save();

            $this->data = array_merge($this->data, $media->getColumns());
        }
    }

    /**
     * Remove a media
     *
     * @param  array $fields
     * @return void
     */
    public function remove(array $fields)
    {
        if (isset($fields['rm_media'])) {
            foreach ($fields['rm_media'] as $id) {
                $media = Table\Media::findById((int)$id);
                if (isset($media->id)) {
                    $library = new MediaLibrary();
                    $library->getById($media->library_id);

                    $folder = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
                    if (file_exists($folder . DIRECTORY_SEPARATOR . $media->file) &&
                        !is_dir($folder . DIRECTORY_SEPARATOR . $media->file)) {
                        unlink($folder . DIRECTORY_SEPARATOR . $media->file);
                    }

                    foreach ($library->actions as $size => $action) {
                        if (file_exists($folder . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $media->file) &&
                            !is_dir($folder . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $media->file)) {
                            unlink($folder . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $media->file);
                        }
                    }
                    $media->delete();
                }
            }
        }
    }

    /**
     * Determine if list of libraries has pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\Media::findAll()->count() > $limit);
    }

    /**
     * Get count of libraries
     *
     * @return int
     */
    public function getCount()
    {
        return Table\Media::findAll()->count();
    }

    /**
     * Process image file
     *
     * @param  string       $fileName
     * @param  MediaLibrary $library
     * @return void
     */
    public function processImage($fileName, $library)
    {
        $class  = 'Pop\Image\\' . $library->adapter;
        $folder = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
        foreach ($library->actions as $size => $action) {
            $sizeFolder = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR .
                $library->folder . DIRECTORY_SEPARATOR . $size;

            if (file_exists($sizeFolder)) {
                $image = new $class($folder . DIRECTORY_SEPARATOR . $fileName);
                $image = call_user_func_array([$image, $action['method']], explode(',', $action['params']));
                $image->setQuality($action['quality']);
                $image->save($sizeFolder . DIRECTORY_SEPARATOR . $fileName);
            }
        }
    }

    /**
     * Get file icon
     *
     * @param  string       $fileName
     * @param  MediaLibrary $library
     * @return string
     */
    public function getFileIcon($fileName, $library)
    {
        $icon      = null;
        $thumbSize = null;
        $folder    = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;

        // Check for the smallest image thumb nail
        foreach ($library->actions as $size => $action) {
            if (file_exists($folder . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $fileName)) {
                $fileSize = filesize($folder . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $fileName);
                if ((null === $thumbSize) || ($fileSize < $thumbSize)) {
                    $thumbSize = $fileSize;
                    $icon      = BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder .
                        DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $fileName;
                }
            }
        }

        if (null === $icon) {
            $iconFolder = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/media/img/icons/50x50/';
            $fileParts  = pathinfo($fileName);
            $ext        = $fileParts['extension'];
            if (!empty($ext)) {
                if (($ext == 'docx') || ($ext == 'pptx') || ($ext == 'xlsx')) {
                    $ext = substr($ext, 0, 3);
                }

                if (file_exists($iconFolder . $ext . '.png')) {
                    $icon = BASE_PATH . CONTENT_PATH . '/assets/media/img/icons/50x50/' . $ext . '.png';
                } else if (($ext == 'wav') || ($ext == 'aif') || ($ext == 'aiff') ||
                    ($ext == 'mp3') || ($ext == 'mp2') || ($ext == 'flac') ||
                    ($ext == 'wma') || ($ext == 'aac') || ($ext == 'swa')) {
                    $icon = BASE_PATH . CONTENT_PATH . '/assets/media/img/icons/50x50/aud.png';
                } else if (($ext == '3gp') || ($ext == 'asf') || ($ext == 'avi') ||
                    ($ext == 'mpg') || ($ext == 'm4v') || ($ext == 'mov') ||
                    ($ext == 'mpeg') || ($ext == 'wmv')) {
                    $icon = BASE_PATH . CONTENT_PATH . '/assets/media/img/icons/50x50/vid.png';
                } else if (($ext == 'bmp') || ($ext == 'ico') || ($ext == 'tiff') || ($ext == 'tif')) {
                    $icon = BASE_PATH . CONTENT_PATH . '/assets/media/img/icons/50x50/img.png';
                } else {
                    $icon = BASE_PATH . CONTENT_PATH . '/assets/media/img/icons/50x50/file.png';
                }
            }
        }

        return $icon;
    }

    /**
     * Add media library types to models of the module config for the application
     *
     * @param  \Phire\Application $application
     * @return void
     */
    public static function addModels(\Phire\Application $application)
    {
        $resources = $application->config()['resources'];
        $params    = $application->services()->getParams('nav.phire');
        $config    = $application->module('Media');
        $libraries = \Media\Table\MediaLibraries::findAll(null, ['order' => 'order ASC']);
        foreach ($libraries->rows() as $library) {
            if (isset($config['models']) && isset($config['models']['Media\Model\Media'])) {
                $config['models']['Media\Model\Media'][] = [
                    'type_field' => 'library_id',
                    'type_value' => $library->id,
                    'type_name'  => $library->name
                ];

                $resources['media-library-' . $library->id . '|media-library-' . str_replace(' ', '-', strtolower($library->name))] = [
                    'index', 'add', 'edit', 'remove'
                ];

                if (!isset($params['tree']['media']['children'])) {
                    $params['tree']['media']['children'] = [];
                }

                $params['tree']['media']['children']['media-library-' . $library->id] = [
                        'name' => $library->name,
                        'href' => '/media/' . $library->id,
                        'acl'  => [
                            'resource'   => 'media-library-' . $library->id,
                            'permission' => 'index'
                        ]
                ];
            }
        }

        $application->mergeConfig(['resources' => $resources]);
        $application->services()->setParams('nav.phire', $params);
        $application->mergeModuleConfig('Media', $config);

    }

}
