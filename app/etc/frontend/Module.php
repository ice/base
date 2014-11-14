<?php

namespace App\Modules\Frontend;

use Ice\Loader;
use Ice\Di\DiInterface;
use Ice\Mvc\ModuleInterface;

/**
 * Frontend module
 *
 * @package     Ice/Base
 * @category    Module
 * @version     1.0
 */
class Module implements ModuleInterface
{

    /**
     * Register a specific autoloader for the module
     *
     * @return void
     */
    public function registerAutoloaders()
    {
        (new Loader())
                ->addNamespace(__NAMESPACE__ . '\Controllers', __DIR__ . '/controllers/')
                ->register();
    }

    /**
     * Register specific services for the module
     *
     * @param object $di Dependency injector
     * @return void
     */
    public function registerServices(DiInterface $di)
    {
        // Set default namespace
        $di->dispatcher->setDefaultNamespace(__NAMESPACE__ . '\Controllers');

        // Overwrite views dirs
        $di->view->setViewsDir(__DIR__ . '/views/');
        $di->view->setPartialsDir('../../../var/views/partials/');
        $di->view->setLayoutsDir('../../../var/views/layouts/');
    }
}
