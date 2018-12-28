<?php

namespace App\Modules\Doc;

use Ice\Di;
use Ice\Loader;
use Ice\Mvc\ModuleInterface;

/**
 * Doc module
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

        $language = $di->i18n->iso($lang);

        // Overwrite views dirs
        $di->view->setViewsDir([
            __DIR__ . '/views/' . $language . '/',
            __DIR__ . '/views/en/',
            __DIR__ . '/views/'
        ]);

        $di->view->setPartialsDir('../../../../../templates/partials/');
        $di->view->setLayoutsDir('../../../../../templates/layouts/');
        $di->view->setLayout('bootstrap');
    }
}
