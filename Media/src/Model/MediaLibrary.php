<?php

namespace Media\Model;

use Phire\Model\AbstractModel;
use Media\Table;

class MediaLibrary extends AbstractModel
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

            return Table\MediaLibraries::findAll(null, [
                'offset' => $page,
                'limit'  => $limit,
                'order'  => $order
            ])->rows();
        } else {
            return Table\MediaLibraries::findAll(null, [
                'order'  => $order
            ])->rows();
        }
    }

    /**
     * Get library by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $library = Table\MediaLibraries::findById($id);
        if (isset($library->id)) {
            $this->data = array_merge($this->data, $library->getColumns());
        }
    }

    /**
     * Save new library
     *
     * @param  array $fields
     * @return void
     */
    public function save(array $fields)
    {
        $library = new Table\MediaLibraries([
            'name' => $fields['name']
        ]);
        $library->save();

        $this->data = array_merge($this->data, $library->getColumns());
    }

    /**
     * Update an existing library
     *
     * @param  array $fields
     * @return void
     */
    public function update(array $fields)
    {
        $library = Table\MediaLibraries::findById($fields['id']);
        if (isset($library->id)) {
            $library->name = $fields['name'];
            $library->save();

            $this->data = array_merge($this->data, $library->getColumns());
        }
    }

    /**
     * Remove a library
     *
     * @param  array $fields
     * @return void
     */
    public function remove(array $fields)
    {
        if (isset($fields['rm_media_libraries'])) {
            foreach ($fields['rm_media_libraries'] as $id) {
                $library = Table\MediaLibraries::findById((int)$id);
                if (isset($library->id)) {
                    $library->delete();
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
        return (Table\MediaLibraries::findAll()->count() > $limit);
    }

    /**
     * Get count of libraries
     *
     * @return int
     */
    public function getCount()
    {
        return Table\MediaLibraries::findAll()->count();
    }

}
