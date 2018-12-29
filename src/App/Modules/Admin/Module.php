<?php

namespace App\Modules\Admin;

use Ice\Di;
use Ice\Loader;
use Ice\Mvc\ModuleInterface;

/**
 * Admin module
 *
 * @category Module
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Module implements ModuleInterface
{

    /**
     * Register a specific autoloader for the module
     *
     * @param object $loader Autoloader
     *
     * @return void
     */
    public function registerAutoloaders(Loader $loader = null)
    {
        // PSR-4
    }

    /**
     * Register specific services for the module
     *
     * @param object $di Dependency injector
     *
     * @return void
     */
    public function registerServices(Di $di)
    {
        // Set default namespace
        $di->dispatcher->setDefaultNamespace(__NAMESPACE__ . '\Controllers');

        // Overwrite views dirs
        $di->view->setViewsDir(__DIR__ . '/views/');
        $di->view->setPartialsDir(__ROOT__ . '/templates/partials/admin/');
        $di->view->setLayoutsDir(__ROOT__ . '/templates/layouts/');
        $di->view->setLayout('bootstrap');
    }
}
