<?php

namespace app;

use Ice\Db;
use Ice\I18n;
use Ice\Loader;
use Ice\Config\Ini as Config;
use Ice\Di\DiInterface;
use Ice\Cli\Router;
use Ice\Cli\Dispatcher;

/**
 * Console application
 *
 * @package     Ice/Base
 * @category    Bootstrap
 * @version     1.0
 */
class Console extends \Ice\Cli\Console
{

    /**
     * Application constructor
     *
     * @param DiInterface $di
     */
    public function __construct(DiInterface $di)
    {
        // Register the app itself as a service
        $di->app = $this;

        // Set the dependency injector
        parent::__construct($di);
    }

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
        $config = new Config(__ROOT__ . '/app/etc/config.ini');

        // Set environment settings
        $config->set('env', (new Config(__ROOT__ . '/app/etc/env.ini'))->{$config->app->env});
        $this->config = $config;

        // Register modules
        $console = $config->modules->console;
        $this->registerModules($config->{$console->modules}->toArray(), $console->default);

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
            ->addNamespace('App\Models', __ROOT__ . '/app/var/models')
            ->addNamespace('App\Libraries', __ROOT__ . '/app/var/lib')
            ->addNamespace('App\Extensions', __ROOT__ . '/app/var/ext')
            ->register();
    }

    /**
     * Set modules and the default module
     *
     * @param  array  $modules
     * @param  string $default
     * @return void
     */
    public function registerModules($modules, $default)
    {
        $this->setModules($modules);
        $this->setDefaultModule($default);
    }

    /**
     * Register services in the dependency injector
     *
     * @return void
     */
    public function registerServices()
    {
        $config = $this->config;
        $this->di->set('config', $config);

        $this->di->set('router', function () {
            $router = new Router();
            $router->setDefaultModule('shell');

            return $router;
        });

        $this->di->dispatcher = new Dispatcher();
        $this->di->i18n = new I18n($config->i18n->toArray());

        $this->di->set('db', function () use ($config) {
            $driver = new Db\Driver\Pdo(
                'mysql:host=' . $config->database->host . ';port=3306;dbname=' . $config->database->dbname,
                $config->database->username,
                $config->database->password
            );
            $driver->getClient()->setAttribute(\Pdo::ATTR_ERRMODE, \Pdo::ERRMODE_EXCEPTION);

            return new Db($driver);
        });
    }

    /**
     * HMVC request in the console
     *
     * @param  array $location location to run the request
     * @return mixed response
     */
    public function request($location)
    {
        $dispatcher = clone $this->getDi()->get('dispatcher');

        if (isset($location['handler'])) {
            $dispatcher->setHandler($location['handler']);
        } else {
            $dispatcher->setHandler('main');
        }

        if (isset($location['action'])) {
            $dispatcher->setAction($location['action']);
        } else {
            $dispatcher->setAction('main');
        }

        if (isset($location['params'])) {
            if (is_array($location['params'])) {
                $dispatcher->setParams($location['params']);
            } else {
                $dispatcher->setParams((array) $location['params']);
            }
        } else {
            $dispatcher->setParams(array());
        }

        return $dispatcher->dispatch();
    }
}
