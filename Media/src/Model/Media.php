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

            return Table\Media::findBy(['library_id' => $this->lid], null, [
                'offset' => $page,
                'limit'  => $limit,
                'order'  => $order
            ])->rows();
        } else {
            return Table\Media::findBy(['library_id' => $this->lid], null, [
                'order'  => $order
            ])->rows();
        }
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
            $this->data = array_merge($this->data, $media->getColumns());
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

        $this->data = array_merge($this->data, $media->getColumns());
    }

    /**
     * Save batch media
     *
     * @param  array $fields
     * @return void
     */
    public function batch(array $fields)
    {
        if (($_FILES) && ($_POST)) {
            foreach ($_FILES as $file) {
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
                if (file_exists($folder . DIRECTORY_SEPARATOR . $fields['current_file'])) {
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
                    if (file_exists($folder . DIRECTORY_SEPARATOR . $media->file)) {
                        unlink($folder . DIRECTORY_SEPARATOR . $media->file);
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
