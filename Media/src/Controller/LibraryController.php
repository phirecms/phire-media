<?php

namespace Media\Controller;

use Media\Model;
use Media\Form;
use Media\Table;
use Phire\Controller\AbstractController;
use Pop\Paginator\Paginator;

class LibraryController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $library = new Model\MediaLibrary();

        if ($library->hasPages($this->config->pagination)) {
            $limit = $this->config->pagination;
            $pages = new Paginator($library->getCount(), $limit);
            $pages->useInput(true);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->prepareView('libraries/index.phtml');
        $this->view->title     = 'Media Libraries';
        $this->view->pages     = $pages;
        $this->view->libraries = $library->getAll(
            $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
        );

        $this->send();
    }

    /**
     * Add action method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView('libraries/add.phtml');
        $this->view->title = 'Media Libraries : Add';

        $fields = $this->application->config()['forms']['Media\Form\MediaLibrary'];

        if (\Pop\Image\Gd::isInstalled()) {
            $fields[0]['adapter']['value']['Gd'] = 'Gd';
        }
        if (\Pop\Image\Imagick::isInstalled()) {
            $fields[0]['adapter']['value']['Imagick'] = 'Imagick';
        }
        if (\Pop\Image\Gmagick::isInstalled()) {
            $fields[0]['adapter']['value']['Gmagick'] = 'Gmagick';
        }

        $this->view->form = new Form\MediaLibrary($fields);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $library = new Model\MediaLibrary();
                $library->save($this->view->form->getFields());
                $this->view->id = $library->id;
                $this->redirect(BASE_PATH . APP_URI . '/media/libraries/edit/' . $library->id . '?saved=' . time());
            }
        }

        $this->send();
    }

    /**
     * Edit action method
     *
     * @param  int $id
     * @return void
     */
    public function edit($id)
    {
        $library = new Model\MediaLibrary();
        $library->getById($id);

        $this->prepareView('libraries/edit.phtml');
        $this->view->title = 'Media Libraries : ' . $library->title;

        $fields = $this->application->config()['forms']['Media\Form\MediaLibrary'];

        if (\Pop\Image\Gd::isInstalled()) {
            $fields[0]['adapter']['value']['Gd'] = 'Gd';
        }
        if (\Pop\Image\Imagick::isInstalled()) {
            $fields[0]['adapter']['value']['Imagick'] = 'Imagick';
        }
        if (\Pop\Image\Gmagick::isInstalled()) {
            $fields[0]['adapter']['value']['Gmagick'] = 'Gmagick';
        }

        $this->view->form = new Form\MediaLibrary($fields);
        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($library->toArray());

        if ($this->request->isPost()) {
            $this->view->form->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $library = new Model\MediaLibrary();
                $library->update($this->view->form->getFields());
                $this->view->id = $library->id;
                $this->redirect(BASE_PATH . APP_URI . '/media/libraries/edit/' . $library->id . '?saved=' . time());
            }
        }

        $this->send();
    }

    /**
     * JSON action method
     *
     * @param  int $id
     * @return void
     */
    public function json($id)
    {
        $json = [];

        $library = Table\MediaLibraries::findById($id);
        if (isset($library->id)) {
            if (null !== $library->actions) {
                $actions = unserialize($library->actions);
                $keys    = array_keys($actions);
                $values  = array_values($actions);
                if ((count($keys) > 1) && (count($values) > 1)) {
                    for ($i = 1; $i < count($keys); $i++) {
                        if (isset($keys[$i]) && isset($values[$i])) {
                            $json[] = [
                                'name'    => $keys[$i],
                                'method'  => $values[$i]['method'],
                                'params'  => $values[$i]['params'],
                                'quality' => $values[$i]['quality']
                            ];
                        }
                    }
                }
            }
        }

        $this->response->setBody(json_encode($json, JSON_PRETTY_PRINT));
        $this->send(200, ['Content-Type' => 'application/json']);
    }

    /**
     * Remove action method
     *
     * @return void
     */
    public function remove()
    {
        if ($this->request->isPost()) {
            $library = new Model\MediaLibrary();
            $library->remove($this->request->getPost());
        }
        $this->redirect(BASE_PATH . APP_URI . '/media/libraries?removed=' . time());
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
