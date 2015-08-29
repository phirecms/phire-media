<?php

namespace Phire\Media\Model;

use Phire\Media\Table;
use Phire\Model\AbstractModel;
use Pop\Archive\Archive;
use Pop\File\Dir;
use Pop\File\Upload;

class Media extends AbstractModel
{

    /**
     * Get all media
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @param  string $title
     * @return array
     */
    public function getAll($limit = null, $page = null, $sort = null, $title = null)
    {
        $order = (null !== $sort) ? $this->getSortOrder($sort, $page) : 'id DESC';

        if (null !== $title) {
            $sql = Table\Media::sql();
            $sql->select()->where('title LIKE :title');

            $by = explode(' ', $order);
            $sql->select()->orderBy($by[0], $by[1]);

            if (null !== $limit) {
                $page = ((null !== $page) && ((int)$page > 1)) ?
                    ($page * $limit) - $limit : null;
                if (null !== $page) {
                    $sql->select()->offset($page);
                }
                $sql->select()->limit($limit);
            }

            $rows = Table\Media::execute((string)$sql, ['title' => '%' . $title . '%'])->rows();
        } else {
            if (null !== $limit) {
                $page = ((null !== $page) && ((int)$page > 1)) ?
                    ($page * $limit) - $limit : null;

                $rows = Table\Media::findBy(['library_id' => $this->lid], [
                    'offset' => $page,
                    'limit'  => $limit,
                    'order'  => $order
                ])->rows();
            } else {
                $rows = Table\Media::findBy(['library_id' => $this->lid], [
                    'order' => $order
                ])->rows();
            }
        }

        $library = new MediaLibrary();
        $library->getById($this->lid);

        foreach ($rows as $key => $value) {
            $icon = $this->getFileIcon($value->file, $library);

            $value->filesize    = $this->formatFileSize($value->size);
            $value->icon        = $icon['image'];
            $value->icon_width  = $icon['width'];
            $value->icon_height = $icon['height'];
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

            $icon = $this->getFileIcon($data['file'], $library);

            $data['filesize']       = $this->formatFileSize($data['size']);
            $data['library_folder'] = $library->folder;
            $data['icon']           = $icon['image'];
            $data['icon_width']     = $icon['width'];
            $data['icon_height']    = $icon['height'];

            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Get media by file
     *
     * @param  string $file
     * @return void
     */
    public function getByFile($file)
    {
        $media = Table\Media::findBy(['file' => $file]);
        if (isset($media->id)) {
            $data = $media->getColumns();

            $library = new MediaLibrary();
            $library->getById($data['library_id']);

            $icon = $this->getFileIcon($data['file'], $library);

            $data['filesize']       = $this->formatFileSize($data['size']);
            $data['library_folder'] = $library->folder;
            $data['icon']           = $icon['image'];
            $data['icon_width']     = $icon['width'];
            $data['icon_height']    = $icon['height'];

            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Get all files
     *
     * @return array
     */
    public function getAllFiles()
    {
        $files = [];
        $f     = $this->getAll();
        foreach ($f as $file) {
            if ((preg_match('/^.*\.(jpg|jpeg|png|gif)$/i', $file->file) == 0)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * Get all images
     *
     * @return array
     */
    public function getAllImages()
    {
        $images = [];
        $img    = $this->getAll();
        foreach ($img as $file) {
            if ((preg_match('/^.*\.(jpg|jpeg|png|gif)$/i', $file->file) == 1)) {
                $images[] = $file;
            }
        }

        return $images;
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
            'file'       => $fileName,
            'size'       => filesize(
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR .
                $library->folder . DIRECTORY_SEPARATOR . $fileName
            ),
            'uploaded'   => date('Y-m-d H:i:s'),
            'order'      => (isset($fields['order'])) ? (int)$fields['order'] : 0
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
        $this->data['ids'] = [];
        foreach ($files as $key => $file) {
            if (($key == 'batch_archive') && !empty($file['name']))  {
                $this->processBatch($file, $fields);
            } else {
                if (!empty($file['name'])) {
                    $this->save($file, $fields);
                    $this->data['ids'][] = $this->data['id'];
                }
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
            $library = new MediaLibrary();
            $library->getById($fields['library_id']);

            if ((null !== $file) && !empty($file['name'])) {
                $folder = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
                if (file_exists($folder . DIRECTORY_SEPARATOR . $fields['current_file']) &&
                    !is_dir($folder . DIRECTORY_SEPARATOR . $fields['current_file'])) {
                    unlink($folder . DIRECTORY_SEPARATOR . $fields['current_file']);
                }
                $fileName = (new Upload($folder))->upload($file);

                if (null !== $library->adapter) {
                    $class     = 'Pop\Image\\' .  $library->adapter;
                    $formats   = array_keys($class::getFormats());
                    $fileParts = pathinfo($fileName);
                    if (!empty($fileParts['extension']) && in_array($fileParts['extension'], $formats)) {
                        $this->processImage($fileName, $library);
                    }
                }
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
            $media->size       = filesize(
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR .
                $library->folder . DIRECTORY_SEPARATOR . $fileName
            );
            $media->uploaded   = date('Y-m-d H:i:s');
            $media->order      = (int)$fields['order'];

            $media->save();

            if (isset($fields['reprocess']) && isset($fields['reprocess'][0])) {
                $this->processImage($fileName, $library);
            }

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

            if (!file_exists($sizeFolder)) {
                mkdir($sizeFolder);
                chmod($sizeFolder, 0777);
                copy(
                    $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html',
                    $sizeFolder . DIRECTORY_SEPARATOR . 'index.html'
                );
            }
            $image = new $class($folder . DIRECTORY_SEPARATOR . $fileName);
            $image = call_user_func_array([$image, $action['method']], explode(',', $action['params']));
            $image->setQuality($action['quality']);
            $image->save($sizeFolder . DIRECTORY_SEPARATOR . $fileName);
        }
    }

    /**
     * Format file size
     *
     * @param  int $size
     * @return string
     */
    public function formatFileSize($size)
    {
        if ($size >= 1000000) {
            $result = round(($size / 1000000), 2) . ' MB';
        } else if (($size < 1000000) && ($size >= 1000)) {
            $result = round(($size / 1000), 2) . ' KB';
        } else if ($size < 1000) {
            $result = $size . ' B';
        }

        return $result;
    }

    /**
     * Get file icon
     *
     * @param  string       $fileName
     * @param  MediaLibrary $library
     * @return array
     */
    public function getFileIcon($fileName, $library)
    {
        $thumbSize = null;
        $folder    = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
        $icon      = [
            'image'  => null,
            'width'  => null,
            'height' => null
        ];

        // Check for the smallest image thumb nail
        foreach ($library->actions as $size => $action) {
            if (file_exists($folder . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $fileName)) {
                $fileSize = filesize($folder . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $fileName);
                if ((null === $thumbSize) || ($fileSize < $thumbSize)) {
                    $thumbSize     = $fileSize;
                    $icon['image'] = BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder .
                        DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $fileName;
                }
            }
        }

        if (null === $icon['image']) {
            $iconFolder = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/phire-media/img/icons/50x50/';
            $fileParts  = pathinfo($fileName);
            $ext        = $fileParts['extension'];
            if (!empty($ext)) {
                if (($ext == 'docx') || ($ext == 'pptx') || ($ext == 'xlsx')) {
                    $ext = substr($ext, 0, 3);
                }

                if (file_exists($iconFolder . $ext . '.png')) {
                    $icon['image'] = BASE_PATH . CONTENT_PATH . '/assets/phire-media/img/icons/50x50/' . $ext . '.png';
                } else if (($ext == 'wav') || ($ext == 'aif') || ($ext == 'aiff') ||
                    ($ext == 'mp3') || ($ext == 'mp2') || ($ext == 'flac') ||
                    ($ext == 'wma') || ($ext == 'aac') || ($ext == 'swa')) {
                    $icon['image'] = BASE_PATH . CONTENT_PATH . '/assets/phire-media/img/icons/50x50/aud.png';
                } else if (($ext == '3gp') || ($ext == 'asf') || ($ext == 'avi') ||
                    ($ext == 'mpg') || ($ext == 'm4v') || ($ext == 'mov') ||
                    ($ext == 'mpeg') || ($ext == 'wmv')) {
                    $icon['image'] = BASE_PATH . CONTENT_PATH . '/assets/phire-media/img/icons/50x50/vid.png';
                } else if (($ext == 'bmp') || ($ext == 'ico') || ($ext == 'tiff') || ($ext == 'tif')) {
                    $icon['image'] = BASE_PATH . CONTENT_PATH . '/assets/phire-media/img/icons/50x50/img.png';
                } else {
                    $icon['image'] = BASE_PATH . CONTENT_PATH . '/assets/phire-media/img/icons/50x50/file.png';
                }
            }
        }

        if ((null !== $icon['image']) && function_exists('getimagesize')) {
            $size = getimagesize($_SERVER['DOCUMENT_ROOT'] . $icon['image']);
            $icon['width']  = $size[0];
            $icon['height'] = $size[1];
        }

        return $icon;
    }

    /**
     * Process batch archive file
     *
     * @param  string $file
     * @param  array  $fields
     * @return void
     */
    public function processBatch($file, array $fields)
    {
        $tmp = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/_tmp';

        mkdir($tmp);
        chmod($tmp, 0777);

        $batchFileName = (new Upload($tmp))->upload($file);
        $archive       = new Archive($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/_tmp/' . $batchFileName);
        $archive->extract($tmp);

        if (($archive->getFilename() != $batchFileName) && file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/_tmp/' . $archive->getFilename())) {
            unlink($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/_tmp/' . $archive->getFilename());
        }

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/_tmp/' . $batchFileName)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/_tmp/' . $batchFileName);
        }

        $library = new MediaLibrary();
        $library->getById($fields['library_id']);
        $settings = $library->getSettings();

        $dir    = new Dir($tmp, true, true, false);
        $upload = new Upload(
            $settings['folder'], $settings['max_filesize'], $settings['disallowed_types'], $settings['allowed_types']
        );
        foreach ($dir->getFiles() as $file) {
            $basename = basename($file);
            $testFile = [
                'name' => $basename,
                'size' => filesize($file),
                'error' => 0
            ];
            if ($upload->test($testFile)) {
                $fileName = $upload->checkFilename($basename);
                copy($file, $settings['folder'] . '/' . $fileName);
                $title = ucwords(str_replace(['_', '-'], [' ', ' '], substr($fileName, 0, strrpos($fileName, '.'))));
                $media = new Table\Media([
                    'library_id' => $fields['library_id'],
                    'title'      => $title,
                    'file'       => $fileName,
                    'size'       => filesize(
                        $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR .
                        $library->folder . DIRECTORY_SEPARATOR . $fileName
                    ),
                    'uploaded'   => date('Y-m-d H:i:s'),
                    'order'      => 0
                ]);
                $media->save();

                $this->data['ids'][] = $media->id;

                if (null !== $library->adapter) {
                    $class = 'Pop\Image\\' . $library->adapter;
                    $formats = array_keys($class::getFormats());
                    $fileParts = pathinfo($fileName);
                    if (!empty($fileParts['extension']) && in_array($fileParts['extension'], $formats)) {
                        $this->processImage($fileName, $library);
                    }
                }
            }
        }

        $dir->emptyDir(true);
    }

}
