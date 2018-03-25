<?php

namespace App\Modules\Shell;

use Ice\Di;
use Ice\Loader;
use Ice\Mvc\ModuleInterface;

/**
 * Shell module.
 *
 * @category Module
 * @package  App
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Module implements ModuleInterface
{

    /**
     * Register a specific autoloader for the module.
     *
     * @return void
     */
    public function registerAutoloaders()
    {
        // PSR-4
    }

    /**
     * Register specific services for the module.
     *
     * @param object $di Dependency injector
     *
     * @return void
     */
    public function registerServices(Di $di)
    {
        // Set default namespace
        $di->dispatcher->setDefaultNamespace(__NAMESPACE__ . '\Tasks');
    }
}
