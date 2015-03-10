<?php

namespace Media\Model;

use Media\Table;
use Phire\Model\AbstractModel;
use Pop\File\Dir;

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
            $data = $library->getColumns();
            $data['max_filesize'] = $this->unparseMaxFilesize($data['max_filesize']);

            if (null !== $data['actions']) {
                $actions = unserialize($data['actions']);
                $keys    = array_keys($actions);
                $values  = array_values($actions);
                if (isset($keys[0]) && isset($values[0])) {
                    $data['action_name_1']    = $keys[0];
                    $data['action_method_1']  = $values[0]['method'];
                    $data['action_params_1']  = $values[0]['params'];
                    $data['action_quality_1'] = $values[0]['quality'];
                }
            }

            $this->data = array_merge($this->data, $data);
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
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $fields['folder'])) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $fields['folder']);
            chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $fields['folder'], 0777);
            copy(
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html',
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $fields['folder'] . DIRECTORY_SEPARATOR . 'index.html'
            );
        }
        $library = new Table\MediaLibraries([
            'name'             => $fields['name'],
            'folder'           => $fields['folder'],
            'allowed_types'    => $fields['allowed_types'],
            'disallowed_types' => $fields['disallowed_types'],
            'max_filesize'     => $this->parseMaxFilesize($fields['max_filesize']),
            'actions'          => serialize($this->parseActions($fields['folder'])),
            'adapter'          => $fields['adapter'],
            'order'            => (int)$fields['order'],
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
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $fields['folder'])) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $fields['folder']);
                chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $fields['folder'], 0777);
                copy(
                    $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html',
                    $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $fields['folder'] . DIRECTORY_SEPARATOR . 'index.html'
                );
            }
            $library->name             = $fields['name'];
            $library->folder           = $fields['folder'];
            $library->allowed_types    = $fields['allowed_types'];
            $library->disallowed_types = $fields['disallowed_types'];
            $library->max_filesize     = $this->parseMaxFilesize($fields['max_filesize']);
            $library->actions          = serialize($this->parseActions($fields['folder']));
            $library->adapter          = $fields['adapter'];
            $library->order            = (int)$fields['order'];
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

    /**
     * Parse max filesize
     *
     * @param  string $size
     * @return int
     */
    protected function parseMaxFilesize($size)
    {
        $size = strtolower($size);

        if (stripos($size, 'm') !== false) {
            $size = (int)trim(substr($size, 0, strpos($size, 'm'))) * 1000000;
        } else if (stripos($size, 'k') !== false) {
            $size = (int)trim(substr($size, 0, strpos($size, 'k'))) * 1000;
        }

        return $size;
    }

    /**
     * Un-parse max filesize
     *
     * @param  int $size
     * @return string
     */
    protected function unparseMaxFilesize($size)
    {
        if ($size >= 1000000) {
            $size = floor($size / 1000000) . ' MB';
        } else if ($size >= 1000) {
            $size = floor($size / 1000) . ' KB';
        }

        return $size;
    }

    /**
     * Parse actions
     *
     * @param  string $folder
     * @return array
     */
    protected function parseActions($folder)
    {
        $dir     = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $folder);
        $curDirs = [];
        $newDirs = [];
        foreach ($dir->getFiles() as $file) {
            if (is_dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $file)) {
                $curDirs[] = $file;
            }
        }

        $actions = [];

        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 12) == 'action_name_') {
                $id = substr($key, (strrpos($key, '_')  + 1));
                if (!empty($_POST['action_name_' . $id]) && ($_POST['action_method_' . $id] != '----') &&
                    !empty($_POST['action_params_' . $id])) {
                    $actions[$_POST['action_name_' . $id]] = [
                        'method'  => $_POST['action_method_' . $id],
                        'params'  => $_POST['action_params_' . $id],
                        'quality' => (!empty($_POST['action_quality_' . $id]) ? (int)$_POST['action_quality_' . $id] : 80)
                    ];

                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $_POST['action_name_' . $id])) {
                        mkdir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $_POST['action_name_' . $id]);
                        chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $_POST['action_name_' . $id], 0777);
                        copy(
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html',
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $_POST['action_name_' . $id] . DIRECTORY_SEPARATOR . 'index.html'
                        );
                    }
                    $newDirs[] = $_POST['action_name_' . $id];
                }
            }
        }

        // Clean up directories
        foreach ($curDirs as $dir) {
            if (!in_array($dir, $newDirs)) {
                $d = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $dir);
                $d->emptyDir(true);
            }
        }


        return $actions;
    }

}
