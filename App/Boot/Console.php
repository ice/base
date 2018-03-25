<?php

namespace App\Boot;

use Ice\Cli\Dispatcher;
use Ice\Cli\Router;
use Ice\Config\Ini;
use Ice\Dump;

/**
 * Base console application.
 *
 * @category Boot
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Console extends \Ice\Cli\Console
{

    /**
     * Initialize the application.
     *
     * @return object Console
     */
    public function initialize()
    {
        // Handle the errors by Error class
        $this->di->errors('App\Boot\Error');

        // Load the config
        $config = new Ini(__ROOT__ . '/App/cfg/config.ini');

        // Set environment settings
        $config->set('env', (new Ini(__ROOT__ . '/App/cfg/env.ini'))->{$config->app->env});
        $this->di->config = $config;

        // Register modules
        $console = $config->modules->console;
        $this->setModules($config->{$console->modules}->toArray());

        // Set services
        $this->di->dump = new Dump(true, true);
        $this->di->dispatcher = new Dispatcher();

        $this->di->set('router', function () use ($config) {
            $router = new Router();
            $router->setDefaultModule($config->modules->console->default);

            return $router;
        });

        return $this;
    }

    /**
     * HMVC request in the console.
     *
     * @param array   $location Location to run the request
     * @param boolean $clear    Clear current dispatcher data
     *
     * @return mixed
     */
    public function request($location, $clear = false)
    {
        $dispatcher = clone $this->di->get('dispatcher');

        if (isset($location['module'])) {
            $dispatcher->setModule($location['module']);
        } elseif ($clear) {
            $dispatcher->setModule($this->di->router->getDefaultModule());
        }

        if (isset($location['handler'])) {
            $dispatcher->setHandler($location['handler']);
        } elseif ($clear) {
            $dispatcher->setHandler($this->di->router->getDefaultHandler());
        }

        if (isset($location['action'])) {
            $dispatcher->setAction($location['action']);
        } elseif ($clear) {
            $dispatcher->setAction($this->di->router->getDefaultActoin());
        }

        if (isset($location['params'])) {
            $dispatcher->setParams($location['params']);
        } elseif ($clear) {
            $dispatcher->setParams([]);
        }

        $this->di->dispatcher = $dispatcher;
        return $this->di->dispatcher->dispatch();
    }
}
