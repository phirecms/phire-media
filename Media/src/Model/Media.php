<?php

namespace Media\Model;

use Media\Table;
use Phire\Model\AbstractModel;

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
     * @param  array $fields
     * @return void
     */
    public function save(array $fields)
    {
        $media = new Table\Media([
            'title' => $fields['title']
        ]);
        $media->save();

        $this->data = array_merge($this->data, $media->getColumns());
    }

    /**
     * Update an existing media
     *
     * @param  array $fields
     * @return void
     */
    public function update(array $fields)
    {
        $media = Table\Media::findById($fields['id']);
        if (isset($media->id)) {
            $media->title = $fields['title'];
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
        $config    = $application->module('Media');
        $libraries = \Media\Table\MediaLibraries::findAll();
        foreach ($libraries->rows() as $library) {
            if (isset($config['models']) && isset($config['models']['Media\Model\Media'])) {
                $config['models']['Media\Model\Media'][] = [
                    'type_field' => 'library_id',
                    'type_value' => $library->id,
                    'type_name'  => $library->name
                ];
            }
        }

        $application->mergeModuleConfig('Media', $config);
    }

}
