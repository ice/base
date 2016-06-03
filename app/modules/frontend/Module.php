<?php

namespace App\Modules\Frontend;

use Ice\Di;
use Ice\Loader;
use Ice\Mvc\ModuleInterface;

/**
 * Frontend module
 *
 * @package     Ice/Base
 * @category    Module
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
    public function registerServices(Di $di)
    {
        // Set default namespace
        $di->dispatcher->setDefaultNamespace(__NAMESPACE__ . '\Controllers');

        // Overwrite views dirs
        $di->view->setViewsDir(__DIR__ . '/views/');
        $di->view->setPartialsDir('../../../views/partials/');
        $di->view->setLayoutsDir('../../../views/layouts/');
        $di->view->setLayout('material');

        // Overwrite flash options
        $di->flash->setOptions([
            "session_key" => "_flash",
            "success" => ["class" => "alert mdl-color--green-50 mdl-color-text--green mdl-shadow--2dp"],
            "info" => ["class" => "alert mdl-color--blue-50 mdl-color-text--blue  mdl-shadow--2dp"],
            "warning" => ["class" => "alert mdl-color--orange-50 mdl-color-text--orange  mdl-shadow--2dp"],
            "danger" => ["class" => "alert mdl-color--red-50 mdl-color-text--red  mdl-shadow--2dp"],
            "html" => true
        ]);
    }
}
