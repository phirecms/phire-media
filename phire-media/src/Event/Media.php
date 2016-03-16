<?php

namespace Phire\Media\Event;

use Phire\Media\Model;
use Phire\Media\Table;
use Pop\Application;
use Phire\Controller\AbstractController;

class Media
{

    /**
     * Bootstrap the module
     *
     * @param  Application $application
     * @return void
     */
    public static function bootstrap(Application $application)
    {
        $resources = $application->config()['resources'];
        $params    = $application->services()->getParams('nav.phire');
        $config    = $application->module('phire-media');
        $models    = (isset($config['models'])) ? $config['models'] : null;
        $libraries = Table\MediaLibraries::findAll(['order' => 'order ASC']);
        foreach ($libraries->rows() as $library) {
            if (null !== $models) {
                if (!isset($models['Phire\Media\Model\Media'])) {
                    $models['Phire\Media\Model\Media'] = [];
                }

                $models['Phire\Media\Model\Media'][] = [
                    'type_field' => 'library_id',
                    'type_value' => $library->id,
                    'type_name'  => $library->name
                ];
            }

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

        $application->mergeConfig(['resources' => $resources]);
        $application->services()->setParams('nav.phire', $params);
        if (null !== $models) {
            $application->module('phire-media')->mergeConfig(['models' => $models]);
        }
    }

    /**
     * Init media model object
     *
     * @param  AbstractController $controller
     * @param  Application        $application
     * @return void
     */
    public static function init(AbstractController $controller, Application $application)
    {
        if ((!$_POST) && ($controller->hasView()) && ($controller->view()->isFile()) &&
            (($controller instanceof \Phire\Content\Controller\IndexController) ||
                ($controller instanceof \Phire\Categories\Controller\IndexController))) {
            $controller->view()->phire->media = new Model\Media();
        }
    }

    /**
     * Parse any media group placeholders
     *
     * @param  AbstractController $controller
     * @param  Application        $application
     * @return void
     */
    public static function parseMedia(AbstractController $controller, Application $application)
    {
        if (($controller->hasView()) &&
            (($controller instanceof \Phire\Categories\Controller\IndexController) ||
                ($controller instanceof \Phire\Content\Controller\IndexController))
        ) {
            $data = $controller->view()->getData();
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $subIds = self::parseLibraryIds($value);
                    if (count($subIds) > 0) {
                        $content = new Model\Media();
                        foreach ($subIds as $sid) {
                            $view = new \Pop\View\View($value, ['media_' . $sid => $content->getAllByLibraryId($sid)]);
                            $controller->view()->{$key} = $view->render();
                        }
                    }
                }
            }

            $body = $controller->response()->getBody();
            $ids  = self::parseLibraryIds($body);
            if (count($ids) > 0) {
                $media = new Model\Media();
                foreach ($ids as $id) {
                    $key = 'media_' . $id;
                    $controller->view()->{$key} = $media->getAllByLibraryId($id);
                }
            }
        }
    }

    /**
     * Parse library IDs from template
     *
     * @param  string $template
     * @return array
     */
    protected static function parseLibraryIds($template)
    {
        $ids   = [];
        $media = [];

        preg_match_all('/\[\{media_.*\}\]/', $template, $media);

        if (isset($media[0]) && isset($media[0][0])) {
            foreach ($media[0] as $m) {
                $ids[] = str_replace('}]', '', substr($m, (strpos($m, '_') + 1)));
            }
        }

        return $ids;
    }

}
