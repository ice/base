<?php

namespace App\Modules\Doc;

use Ice\Di;
use Ice\Loader;
use Ice\Mvc\ModuleInterface;

/**
 * Documentation module
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

        // Get the language
        if ($di->session->has('lang')) {
            // Set the language from session
            $lang = $di->session->get('lang');
        } elseif ($di->cookies->has('lang')) {
            // Set the language from cookie
            $lang = $di->cookies->get('lang');
        } else {
            // Default language
            $lang = $di->i18n->lang();
        }

        // Overwrite views dirs
        $di->view->setViewsDir(__DIR__ . '/views/' . $di->i18n->iso($lang) . '/');
        $di->view->setPartialsDir('../../../../views/partials/');
        $di->view->setLayoutsDir('../../../../views/layouts/');
    }
}
