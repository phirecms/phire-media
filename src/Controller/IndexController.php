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
namespace Phire\Media\Controller;

use Phire\Media\Model;
use Phire\Media\Form;
use Phire\Media\Table;
use Phire\Controller\AbstractController;
use Pop\File\Upload;
use Pop\Paginator\Paginator;
use Pop\Web\Browser;

/**
 * Media Index Controller class
 *
 * @category   Phire\Media
 * @package    Phire\Media
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @param  int $lid
     * @return void
     */
    public function index($lid = null)
    {
        if (null === $lid) {
            $this->prepareView('media/libraries.phtml');
            $library = new Model\MediaLibrary();

            if ($library->hasPages($this->config->pagination)) {
                $limit = $this->config->pagination;
                $pages = new Paginator($library->getCount(), $limit);
                $pages->useInput(true);
            } else {
                $limit = null;
                $pages = null;
            }

            $this->view->title     = 'Media';
            $this->view->pages     = $pages;
            $this->view->libraries = $library->getAll(
                $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
            );
        } else {
            $this->prepareView('media/index.phtml');
            $media   = new Model\Media(['lid' => $lid]);
            $library = new Model\MediaLibrary();
            $library->getById($lid);

            if (!isset($library->id)) {
                $this->redirect(BASE_PATH . APP_URI . '/media');
            }

            if ($this->services['acl']->isAllowed($this->sess->user->role, 'media-library-' . $library->id, 'index')) {
                if (null !== $this->request->getQuery('title')) {
                    $mediaFiles = $media->getAll(
                        null, $this->request->getQuery('page'), $this->request->getQuery('sort'), $this->request->getQuery('title')
                    );
                    if (count($mediaFiles) > $this->config->pagination) {
                        $page  = $this->request->getQuery('page');
                        $limit = $this->config->pagination;
                        $pages = new Paginator(count($mediaFiles), $limit);
                        $pages->useInput(true);
                        $offset = ((null !== $page) && ((int)$page > 1)) ?
                            ($page * $limit) - $limit : 0;
                        $mediaFiles = array_slice($mediaFiles, $offset, $limit, true);
                    } else {
                        $pages = null;
                    }
                } else {
                    if ($media->hasPages($this->config->pagination, $lid)) {
                        $limit = $this->config->pagination;
                        $pages = new Paginator($media->getCount($lid), $limit);
                        $pages->useInput(true);
                    } else {
                        $limit = null;
                        $pages = null;
                    }
                    $mediaFiles = $media->getAll(
                        $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
                    );
                }

                $this->view->title       = 'Media : ' . $library->name;
                $this->view->pages       = $pages;
                $this->view->lid         = $lid;
                $this->view->folder      = $library->folder;
                $this->view->searchValue = htmlentities(strip_tags($this->request->getQuery('title')), ENT_QUOTES, 'UTF-8');
                $this->view->media       = $mediaFiles;
            } else {
                $this->redirect(BASE_PATH . APP_URI . '/media');
            }
        }

        $this->send();
    }

    /**
     * Add action method
     *
     * @param  int $lid
     * @return void
     */
    public function add($lid)
    {
        $library = new Model\MediaLibrary();
        $library->getById($lid);

        if (!isset($library->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/media');
        }

        $this->prepareView('media/add.phtml');
        $this->view->title = 'Media : ' . $library->name . ' : Add';
        $this->view->lid   = $lid;
        $this->view->max   = $library->getMaxFilesize();

        $fields = $this->application->config()['forms']['Phire\Media\Form\Media'];
        $fields[0]['library_id']['value'] = $lid;
        $this->view->form = new Form\Media($fields);

        if ($this->request->isPost()) {
            $settings = $library->getSettings();
            $folder   = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
            if (!file_exists($folder)) {
                $library->createFolder($library->folder);
            }

            $values   = (!empty($_FILES['file']) && !empty($_FILES['file']['name'])) ?
                array_merge($this->request->getPost(), ['file' => $_FILES['file']['name']]) :
                $this->request->getPost();

            $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($values, $settings);

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $media = new Model\Media();
                $media->save($_FILES['file'], $this->view->form->getFields());
                $this->view->id = $media->id;
                $this->sess->setRequestValue('saved', true);
                $this->redirect(BASE_PATH . APP_URI . '/media/edit/' . $lid . '/'. $media->id);
            }
        }

        $this->send();
    }

    /**
     * Edit action method
     *
     * @param  int $lid
     * @param  int $id
     * @return void
     */
    public function edit($lid, $id)
    {
        $library = new Model\MediaLibrary();
        $library->getById($lid);

        if (!isset($library->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/media');
        }

        $media = new Model\Media();
        $media->getById($id);

        if (!isset($media->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/media/' . $lid);
        }

        $this->prepareView('media/edit.phtml');
        $this->view->title       = 'Media';
        $this->view->media_title = $media->title;
        $this->view->lid         = $lid;
        $this->view->max         = $library->getMaxFilesize();

        $fields = $this->application->config()['forms']['Phire\Media\Form\Media'];
        $fields[0]['library_id']['value'] = $lid;
        $fields[0]['reprocess']['type']   = 'checkbox';
        $fields[0]['reprocess']['value']  = ['1' => 'Re-process?'];

        $values = $media->toArray();
        $fields[1]['file']['label'] = 'Replace File?';
        unset($fields[1]['file']['required']);

        $fields[1]['title']['attributes']['onkeyup'] = 'phire.changeTitle(this.value);';

        $width = ((null !== $media->icon_width) && ($media->icon_width < 120)) ?
            $media->icon_width : 120;

        $fileName = (strlen($values['file']) > 20) ? substr($values['file'], 0, 20) . '...' : $values['file'];

        $fields[0]['current_file']['value'] = $values['file'];
        $fields[0]['current_file']['label'] = '<span class="media-view-link"><a title="' . $values['file'] . '" href="' .
            BASE_PATH . CONTENT_PATH . '/' . $library->folder . '/' . $media->file .
            '" target="_blank"><img src="' . $media->icon . '" width="' . $width . '" />' . $fileName .
            '</a><span class="media-file-size">[ ' . $media->filesize . ' ]</span></span>';

        unset($values['file']);

        $this->view->form = new Form\Media($fields);
        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($values);

        if ($this->request->isPost()) {
            $settings = $library->getSettings();
            $folder   = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
            if (!file_exists($folder)) {
                $library->createFolder($library->folder);
            }

            $values   = (!empty($_FILES['file']) && !empty($_FILES['file']['name'])) ?
                array_merge($this->request->getPost(), ['file' => $_FILES['file']['name']]) :
                $this->request->getPost();

            $this->view->form->setFieldValues($values, $settings);

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $media = new Model\Media();
                $media->update((!empty($_FILES['file']) ? $_FILES['file'] : null), $this->view->form->getFields());
                $this->view->id = $media->id;
                $this->sess->setRequestValue('saved', true);
                $this->redirect(BASE_PATH . APP_URI . '/media/edit/' . $lid . '/'. $media->id);
            }
        }

        $this->send();
    }

    /**
     * batch action method
     *
     * @param  int $lid
     * @return void
     */
    public function batch($lid)
    {
        $library = new Model\MediaLibrary();
        $library->getById($lid);

        if (!isset($library->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/media');
        }

        $browser     = new Browser();
        $dragAndDrop = !(($browser->isMsie()) && ($browser->getVersion() <= 9));

        if ((null !== $this->request->getQuery('basic')) && ($this->request->getQuery('basic'))) {
            $this->prepareView('media/batch.phtml');
            $this->view->title       = 'Media : ' . $library->name . ' : Batch Upload';
            $this->view->lid         = $lid;
            $this->view->max         = $library->getMaxFilesize();
            $this->view->dragAndDrop = $dragAndDrop;

            $fields = $this->application->config()['forms']['Phire\Media\Form\Batch'];
            $fields[0]['library_id']['value'] = $lid;

            $fields[2]['batch_archive']['label'] = 'Batch Archive File <span class="batch-formats">(' . implode(', ', array_keys(\Pop\Archive\Archive::getFormats())) . ')</span>';
            $fields[2]['batch_archive']['validators'] = new \Pop\Validator\RegEx(
                '/^.*\.(' . implode('|', array_keys(\Pop\Archive\Archive::getFormats())) . ')$/i',
                'That file archive type is not allowed.'
            );

            $this->view->form = new Form\Batch($fields);

            if ($this->request->isPost()) {
                $settings = $library->getSettings();
                $folder = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
                if (!file_exists($folder)) {
                    $library->createFolder($library->folder);
                }

                $values = (!empty($_FILES['file_1']) && !empty($_FILES['file_1']['name'])) ?
                    array_merge($this->request->getPost(), ['file_1' => $_FILES['file_1']['name']]) :
                    $this->request->getPost();

                $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                     ->setFieldValues($values, $settings);

                if ($this->view->form->isValid()) {
                    $this->view->form->clearFilters()
                         ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                         ->filter();
                    $media = new Model\Media();
                    $media->batch($_FILES, $this->view->form->getFields());
                    $this->view->id = $media->ids;
                    $this->sess->setRequestValue('saved', true);
                    $this->redirect(BASE_PATH . APP_URI . '/media/' . $lid . '?basic=1');
                }
            }
        } else if (!$dragAndDrop) {
            $this->redirect(BASE_PATH . APP_URI . '/media/batch/' . $lid . '?basic=1');
        } else {
            $this->prepareView('media/batch-ajax.phtml');
            $this->view->title              = 'Media : ' . $library->name . ' : Batch Upload';
            $this->view->lid                = $lid;
            $this->view->max                = $library->getMaxFilesize();
            $this->view->categoriesForBatch = null;

            if (class_exists('Phire\Categories\Model\Category')) {
                $config = $this->application->module('phire-categories');
                $cat    = new \Phire\Categories\Model\Category([], $config);
                $cat->getAll();

                if (count($cat->getFlatMap()) > 0) {
                    $categoryValues = $cat->getCategoryValues();
                    $categories = new \Pop\Form\Element\CheckboxSet('categories', $categoryValues);
                    $categories->setLabel('Categories');
                    $this->view->categoriesForBatch = $categories;
                }
            }
        }

        $this->send();
    }

    /**
     * Remove action method
     *
     * @param  int $lid
     * @return void
     */
    public function remove($lid)
    {
        if ($this->request->isPost()) {
            $media = new Model\Media();
            $media->remove($this->request->getPost());
        }
        $this->sess->setRequestValue('removed', true);
        $this->redirect(BASE_PATH . APP_URI . '/media/' . $lid);
    }

    /**
     * Ajax action method
     *
     * @param  int $lid
     * @return void
     */
    public function ajax($lid)
    {
        $library = new Model\MediaLibrary();
        $library->getById($lid);

        $json       = [];
        $categories = [];

        if (class_exists('Phire\Categories\Model\Category') && isset($_POST['categories'])) {
            $categories = explode(',', $_POST['categories']);
        }

        if (!isset($library->id)) {
            $json['error'] = 'That library was not found.';
        } else {
            $settings = $library->getSettings();
            $folder   = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . $library->folder;
            if (!file_exists($folder)) {
                $library->createFolder($library->folder);
            }

            $upload = new Upload(
                $settings['folder'], $settings['max_filesize'], $settings['disallowed_types'], $settings['allowed_types']
            );
            foreach ($_FILES as $key => $file) {
                if (!empty($file['name'])) {
                    $json['id'] = substr($key, (strrpos($key, '_') + 1));
                    if (!$upload->test($file)) {
                        $json['error'] = $upload->getErrorMessage();
                    } else {
                        $media = new Model\Media();
                        $media->save($file, ['library_id' => $lid]);

                        if (count($categories) > 0) {
                            foreach ($categories as $category) {

                                $catItem = new \Phire\Categories\Table\CategoryItems([
                                    'category_id' => (int)$category,
                                    'content_id'  => null,
                                    'media_id'    => $media->id,
                                    'order'       => 0
                                ]);
                                $catItem->save();
                            }
                        }
                    }
                }
            }
        }

        $this->response->setBody(json_encode($json, JSON_PRETTY_PRINT));
        $this->send(200, ['Content-Type' => 'application/json']);
    }

    /**
     * Browser action method
     *
     * @param  int $lid
     * @return void
     */
    public function browser($lid = null)
    {
        if ((null !== $this->request->getQuery('editor')) && (null !== $this->request->getQuery('type'))) {
            $this->prepareView('media/browser.phtml');
            $this->view->title = 'Media Browser';

            if ($this->request->isPost()) {
                $library = new Model\MediaLibrary();
                $library->getById($lid);

                $settings = $library->getSettings();

                if (count($settings) == 4) {
                    $upload = new \Pop\File\Upload(
                        $settings['folder'], $settings['max_filesize'], $settings['disallowed_types'], $settings['allowed_types']
                    );
                    if ($upload->test($_FILES['file'])) {
                        $media = new Model\Media();
                        $media->save($_FILES['file'], $this->request->getPost());
                        $this->sess->setRequestValue('saved', true);
                        $this->redirect(str_replace('&error=1', '', $_SERVER['REQUEST_URI']));
                    } else {
                        $this->redirect(str_replace('&error=1', '', $_SERVER['REQUEST_URI']) . '&error=1');
                    }
                }
            }

            if ((null !== $lid) && (null !== $this->request->getQuery('asset')) && (null !== $this->request->getQuery('asset_type'))) {
                $assets = [];
                $limit  = $this->config->pagination;
                $page   = $this->request->getQuery('page');
                $pages  = null;

                $library = new Model\MediaLibrary();

                if (($this->request->getQuery('asset_type') == 'content') && ($this->application->isRegistered('phire-content'))) {
                    $type    = \Phire\Content\Table\ContentTypes::findById($lid);
                    $content = \Phire\Content\Table\Content::findBy(['type_id' => $lid], ['order' => 'order, id ASC']);
                    foreach ($content->rows() as $c) {
                        $assets[] = [
                            'id'    => $c->id,
                            'title' => $c->title,
                            'uri'   => BASE_PATH . $c->uri
                        ];
                    }

                    if (isset($type->id)) {
                        $this->view->assetType = $type->name;
                    }
                } else if ($this->request->getQuery('asset_type') == 'media') {
                    $library->getById($lid);
                    $media = new Model\Media(['lid' => $lid]);
                    if ($this->request->getQuery('asset') == 'file') {
                        $assets = $media->getAll();
                    } else if ($this->request->getQuery('asset') == 'image') {
                        $assets = $media->getAllImages();
                    }

                    $this->view->assetType = $library->name;
                }

                if (count($assets) > $limit) {
                    $pages  = new Paginator(count($assets), $limit);
                    $pages->useInput(true);
                    $offset = ((null !== $page) && ((int)$page > 1)) ?
                        ($page * $limit) - $limit : 0;
                    $assets = array_slice($assets, $offset, $limit, true);
                }

                $this->view->title         = 'Media' . ((null !== $this->view->assetType) ? ' : '. $this->view->assetType : null);
                $this->view->lid           = $lid;
                $this->view->folder        = $library->folder;
                $this->view->sizes         = (null !== $library->actions) ? array_keys($library->actions) : [];
                $this->view->pages         = $pages;
                $this->view->browserAssets = $assets;
            } else {
                $libraries = [];
                $limit     = null;
                $pages     = null;

                if (($this->request->getQuery('type') == 'file') && ($this->application->isRegistered('phire-content'))) {
                    $types = \Phire\Content\Table\ContentTypes::findAll(['order' => 'order ASC']);
                    if ($types->hasRows()) {
                        $libraries['Content'] = [];
                        foreach ($types->rows() as $type) {
                            $libraries['Content'][$type->id] = $type->name;
                        }
                    }
                }

                $libraries['Media'] = [];
                $library = new Model\MediaLibrary();
                $libs    = $library->getAll();
                foreach ($libs as $lib) {
                    $libraries['Media'][$lib->id] = $lib->name;
                }

                $this->view->title     = 'Media';
                $this->view->pages     = $pages;
                $this->view->lid       = $lid;
                $this->view->libraries = $libraries;
            }

            $this->send();
        } else {
            $this->redirect(BASE_PATH . APP_URI . '/media');
        }
    }

    /**
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->viewPath = __DIR__ . '/../../view';
        parent::prepareView($template);
    }

}
