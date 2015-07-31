<?php

namespace app;

use Ice\Cli\Dispatcher;
use Ice\Cli\Router;
use Ice\Config\Ini as Config;
use Ice\Db;
use Ice\Di;
use Ice\I18n;
use Ice\Loader;
use Ice\Mvc\Url;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Mvc\View\Engine\Sleet\Compiler;
use Ice\Tag;

/**
 * Console application
 *
 * @package     Ice/Base
 * @category    Bootstrap
 */
class Console extends \Ice\Cli\Console
{

    /**
     * Initialize the application
     *
     * @return Console
     */
    public function initialize()
    {
        // Register an autoloader
        $this->registerLoader();

        // Load the config
        $config = new Config(__ROOT__ . '/app/cfg/config.ini');

        // Set environment settings
        $config->set('env', (new Config(__ROOT__ . '/app/cfg/env.ini'))->{$config->app->env});
        $this->config = $config;

        // Register modules
        $console = $config->modules->console;
        $this->setModules($config->{$console->modules}->toArray());

        // Register services
        $this->registerServices();

        return $this;
    }

    /**
     * Register autoloaders
     *
     * @return void
     */
    public function registerLoader()
    {
        (new Loader())
            ->addNamespace('App\Models', __ROOT__ . '/app/models')
            ->addNamespace('App\Libraries', __ROOT__ . '/app/lib')
            ->addNamespace('App\Extensions', __ROOT__ . '/app/ext')
            ->register();
    }

    /**
     * Register services in the dependency injector
     *
     * @return void
     */
    public function registerServices()
    {
        $config = $this->config;
        $this->di->config = $config;

        $this->di->i18n = new I18n($config->i18n->toArray());

        // Set the url service
        $this->di->set('url', function () use ($config) {
            $url = new Url();
            $url->setBaseUri($config->app->base_uri);
            $url->setStaticUri($config->app->static_uri);
            return $url;
        });

        $this->di->tag = new Tag();
        $this->di->dispatcher = new Dispatcher();

        $this->di->set('router', function () {
            $router = new Router();
            $router->setDefaultModule('shell');

            return $router;
        });

        // Set the db service
        $this->di->set('db', function () use ($config) {
            $driver = new Db\Driver\Pdo(
                'mysql:host=' . $config->database->host . ';port=3306;dbname=' . $config->database->dbname,
                $config->database->username,
                $config->database->password
            );
            $driver->getClient()->setAttribute(\Pdo::ATTR_ERRMODE, \Pdo::ERRMODE_EXCEPTION);
            return new Db($driver);
        });

        // Set the view service
        $this->di->set('view', function () {
            $view = new View();
            $view->setViewsDir(__ROOT__ . '/app/views/');

            // Options for Sleet template engine
            $sleet = new Sleet($view, $this->di);
            $sleet->setOptions([
                'compileDir' => __ROOT__ . '/app/tmp/sleet/',
                'trimPath' => __ROOT__,
                'compile' => Compiler::IF_CHANGE
            ]);

            // Set template engines
            $view->setEngines([
                '.md' => 'App\Libraries\Markdown',
                '.sleet' => $sleet,
                '.phtml' => 'Ice\Mvc\View\Engine\Php'
            ]);

            return $view;
        });
    }

    /**
     * HMVC request in the console
     *
     * @param array $location Location to run the request
     * @param boolean $clear Clear current dispatcher data
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
