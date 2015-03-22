<?php

namespace Media\Event;

use Media\Table;
use Pop\Application;

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
        $config    = $application->module('Media');
        $models    = (isset($config['models'])) ? $config['models'] : null;
        $libraries = Table\MediaLibraries::findAll(null, ['order' => 'order ASC']);
        foreach ($libraries->rows() as $library) {
            if (null !== $models) {
                if (!isset($models['Media\Model\Media'])) {
                    $models['Media\Model\Media'] = [];
                }

                $models['Media\Model\Media'][] = [
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
            $application->module('Media')->mergeConfig(['models' => $models]);
        }
    }

}
