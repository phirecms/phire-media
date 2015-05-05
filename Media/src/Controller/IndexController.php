<?php

namespace Media\Controller;

use Media\Model;
use Media\Form;
use Media\Table;
use Phire\Controller\AbstractController;
use Pop\Paginator\Paginator;

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
                if ($media->hasPages($this->config->pagination)) {
                    $limit = $this->config->pagination;
                    $pages = new Paginator($media->getCount(), $limit);
                    $pages->useInput(true);
                } else {
                    $limit = null;
                    $pages = null;
                }

                $this->view->title = 'Media : ' . $library->name;
                $this->view->pages = $pages;
                $this->view->lid = $lid;
                $this->view->folder = $library->folder;
                $this->view->media = $media->getAll(
                    $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
                );
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

        $fields = $this->application->config()['forms']['Media\Form\Media'];
        $fields[0]['library_id']['value'] = $lid;
        $this->view->form = new Form\Media($fields);

        if ($this->request->isPost()) {
            $settings = $library->getSettings();
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
                $this->redirect(BASE_PATH . APP_URI . '/media/edit/' . $lid . '/'. $media->id . '?saved=' . time());
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

        $this->prepareView('media/batch.phtml');
        $this->view->title = 'Media : ' . $library->name . ' : Batch Upload';
        $this->view->lid   = $lid;
        $this->view->max   = $library->getMaxFilesize();

        $fields = $this->application->config()['forms']['Media\Form\Batch'];
        $fields[0]['library_id']['value'] = $lid;

        $fields[2]['batch_archive']['label']      = 'Batch Archive File <span class="batch-formats">(' . implode(', ', array_keys(\Pop\Archive\Archive::getFormats())) .')</span>';
        $fields[2]['batch_archive']['validators'] = new \Pop\Validator\RegEx(
            '/^.*\.(' . implode('|', array_keys(\Pop\Archive\Archive::getFormats())) . ')$/i',
            'That file archive type is not allowed.'
        );

        $this->view->form = new Form\Batch($fields);

        if ($this->request->isPost()) {
            $settings = $library->getSettings();
            $values   = (!empty($_FILES['file_1']) && !empty($_FILES['file_1']['name'])) ?
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
                $this->redirect(BASE_PATH . APP_URI . '/media/' . $lid . '?saved=' . time());
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

        $fields = $this->application->config()['forms']['Media\Form\Media'];
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
            '" target="_blank"><img src="' . $media->icon . '" width="' . $width . '" />' . $fileName . '</a></span>';

        unset($values['file']);

        $this->view->form = new Form\Media($fields);
        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($values);

        if ($this->request->isPost()) {
            $settings = $library->getSettings();
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
                $this->redirect(BASE_PATH . APP_URI . '/media/edit/' . $lid . '/'. $media->id . '?saved=' . time());
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
        $this->redirect(BASE_PATH . APP_URI . '/media/' . $lid .  '?removed=' . time());
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

            $library = new Model\MediaLibrary();
            if (null !== $lid) {
                $media   = new Model\Media(['lid' => $lid]);
                $library->getById($lid);

                if ($this->services['acl']->isAllowed($this->sess->user->role, 'media-library-' . $library->id, 'index')) {
                    if ($media->hasPages($this->config->pagination)) {
                        $limit = $this->config->pagination;
                        $pages = new Paginator($media->getCount(), $limit);
                        $pages->useInput(true);
                    } else {
                        $limit = null;
                        $pages = null;
                    }

                    $this->view->title = 'Media : ' . $library->name;
                    $this->view->pages = $pages;
                    $this->view->lid = $lid;
                    $this->view->folder = $library->folder;
                    $this->view->sizes = (null !== $library->actions) ? array_keys($library->actions) : [];
                    $this->view->media = $media->getAll(
                        $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
                    );
                } else {
                    $this->redirect(BASE_PATH . APP_URI . '/media/browser?' . http_build_query($_GET));
                }
            } else {
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
            }

            $this->send();
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
