<?php

namespace App\Modules\Shell;

use Ice\Loader;
use Ice\Di;
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
                ->addNamespace(__NAMESPACE__ . '\Tasks', __DIR__ . '/tasks/')
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
        $di->dispatcher->setDefaultNamespace(__NAMESPACE__ . '\Tasks');
    }
}
