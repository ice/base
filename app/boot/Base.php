<?php

namespace App;

use Ice\Auth\Driver\Model as Auth;
use Ice\Config\Ini;
use Ice\Db;
use Ice\I18n;
use Ice\Mvc\App;
use Ice\Mvc\Router;
use Ice\Mvc\Url;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;

/**
 * Mvc application
 *
 * @package     Ice/Base
 * @category    Bootstrap
 */
class Base extends App
{

    /**
     * Meta description
     * @var string
     */
    public $description;

    /**
     * Meta keywords
     * @var string
     */
    public $keywords;

    /**
     * Initialize the application
     *
     * @return Application
     */
    public function initialize()
    {
        // Register an autoloader
        $this->registerLoader();

        // Load the config
        $config = new Ini(__ROOT__ . '/app/cfg/config.ini');

        // Set environment settings
        $config->set('env', (new Ini(__ROOT__ . '/app/cfg/env.ini'))->{$config->app->env});
        $config->set('assets', new Ini(__ROOT__ . '/app/cfg/assets.ini'));
        $this->config = $config;

        // Register modules
        $application = $config->modules->application;
        $this->setModules($config->{$application->modules}->toArray());

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
        $this->di->loader
            ->addNamespace('App\Models', __ROOT__ . '/app/models')
            ->addNamespace('App\Libraries', __ROOT__ . '/app/lib')
            ->addNamespace('App\Extensions', __ROOT__ . '/app/ext')
            ->addNamespace('App\Services', __ROOT__ . '/app/services')
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

        if ($config->app->env == "development") {
            $this->dump->setDetailed(true);
        }

        $this->di->crypt->setKey($config->crypt->key);
        $this->di->cookies->setSalt($config->cookie->salt);
        $this->di->i18n = new I18n($config->i18n->toArray());
        $this->di->auth = new Auth($config->auth->toArray());

        // Set the url service
        $this->di->set('url', function () use ($config) {
            $url = new Url();
            $url->setBaseUri($config->app->base_uri);
            $url->setStaticUri($config->app->static_uri);
            return $url;
        });

        // Set the assets service
        $this->di->assets->setOptions([
            'source' => __ROOT__ . '/public/',
            'target' => 'min/',
            'minify' => $config->env->assets->minify
        ]);

        // Set the dispatcher service
        $this->di->dispatcher->setSilent($config->env->silent->dispatcher);

        // Set the router service
        $this->di->set('router', function () use ($config) {
            $router = new Router();
            $router->setDefaultModule($config->modules->application->default);
            $router->setSilent($config->env->silent->router);
            $router->setRoutes((new Routes())->universal());
            return $router;
        });

        // Set the db service
        $this->di->set('db', function () use ($config) {
            $db = new Db(
                $config->database->type,
                $config->database->host,
                $config->database->port,
                $config->database->name,
                $config->database->user,
                $config->database->password
            );

            if ($config->database->type !== "mongodb" && $config->app->env == "development") {
                $db->getDriver()->getClient()->setAttribute(\Pdo::ATTR_ERRMODE, \Pdo::ERRMODE_EXCEPTION);
            }

            return $db;
        });

        // Set the view service
        $this->di->set('view', function () use ($config) {
            $view = new View();
            $view->setViewsDir(__ROOT__ . '/app/views/');

            // Options for Sleet template engine
            $sleet = new Sleet($view, $this->di);
            $sleet->setOptions([
                'compileDir' => __ROOT__ . '/app/tmp/sleet/',
                'trimPath' => __ROOT__,
                'compile' => $config->env->sleet->compile
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
     * Overwrite response by display pretty view
     *
     * @param string $method
     * @param string $uri
     * @return object response
     */
    public function handle($method = null, $uri = null)
    {
        $di = $this->di;

        $this->di->hook('app.after.handle', function ($response) use ($di) {
            // Display pretty view for some response codes
            if (!$response->isInformational() && !$response->isSuccessful() && !$response->isRedirect()) {
                $code = $response->getStatus();
                $response->setBody(Error::view($di, $code, $response->getMessage($code)));
            }
        });

        return parent::handle($method, $uri);
    }

    /**
     * HMVC request in the application
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
