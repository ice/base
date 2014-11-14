<?php

namespace App;

use Ice\Config\Ini as Config;
use Ice\Auth\Driver\Model as Auth;
use Ice\Di\DiInterface;
use Ice\Db;
use Ice\I18n;
use Ice\Dump;
use Ice\Crypt;
use Ice\Flash;
use Ice\Loader;
use Ice\Filter;
use Ice\Session;
use Ice\Http\Request;
use Ice\Http\Response;
use Ice\Cookies;
use Ice\Mvc\Url;
use Ice\Mvc\Router;
use Ice\Mvc\Dispatcher;
use Ice\Tag;
use Ice\Mvc\App;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Mvc\View\Engine\Sleet\Compiler;

/**
 * Mvc application
 *
 * @package     Ice/Base
 * @category    Bootstrap
 * @version     1.0
 */
class Application extends App
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
     * @return Application
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
        $application = $config->modules->application;
        $this->registerModules($config->{$application->modules}->toArray(), $application->default);

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
     * @param array $modules
     * @param string $default
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
        $this->di->config = $config;
        $this->di->dump = new Dump(true);
        $this->di->crypt = new Crypt($config->crypt->key);
        $this->di->filter = new Filter();
        $this->di->session = new Session();
        $this->di->request = new Request();
        $this->di->cookies = new Cookies($config->cookie->salt);
        $this->di->response = new Response();

        $this->di->i18n = new I18n($config->i18n->toArray());
        $this->di->auth = new Auth($config->auth->toArray());

        // Set the url service
        $this->di->set('url', function () use ($config) {
            $url = new Url();
            $url->setBaseUri($config->app->base_uri);
            $url->setStaticUri($config->app->static_uri);
            return $url;
        });

        $this->di->tag = new Tag();
        $this->di->flash = new Flash();

        // Set the dispatcher service
        $this->di->set('dispatcher', function () use ($config) {
            $dispatcher = new Dispatcher();
            $dispatcher->setSilent($config->env->silent->dispatcher);
            return $dispatcher;
        });

        // Set the router service
        $this->di->set('router', function () use ($config) {
            $router = new Router();
            $router->setDefaultModule('frontend');
            $router->setSilent($config->env->silent->router);
            $router->setRoutes((new Routes())->universal());
            return $router;
        });

        // Set the db service
        $this->di->set('db', function () use ($config) {
            $driver = new Db\Driver\Pdo('mysql:host=' . $config->database->host . ';port=3306;dbname=' . $config->database->dbname, $config->database->username, $config->database->password);
            //$driver->getClient()->setAttribute(\Pdo::ATTR_ERRMODE, \Pdo::ERRMODE_EXCEPTION);
            return new Db($driver);
        });

        // Set the view service
        $this->di->set('view', function () {
            $view = new View();
            $view->setViewsDir(__ROOT__ . '/app/var/views/');

            // Options for Sleet template engine
            $sleet = new Sleet($view, $this->di);
            $sleet->setOptions([
                'compileDir' => __ROOT__ . '/app/var/tmp/sleet/',
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
     * Overwrite response by display pretty view
     *
     * @param string $method
     * @param string $uri
     * @return object response
     */
    public function handle($method = null, $uri = null)
    {
        $view = $this->di->view;
        $assets['styles'] = [
            $this->di->tag->link(['css/bootstrap.min.css?v=3.3.0']),
            $this->di->tag->link(['css/fonts.css']),
            $this->di->tag->link(['css/app.css'])
        ];

        // Display pretty view if response is Client/Server Error and silet option is true
        $this->di->hook('app.after.handle', function ($response) use ($view, $assets) {
            $status = $response->getStatus();

            if ($response->isClientError() || $response->isServerError()) {
                $view->setVars([
                    'code' => $status,
                    'message' => $response->getMessage($status)
                ]);
                switch ($status) {
                    case 404:
                        $view->setVar('icon', 'road');
                        break;
                    case 508:
                        $view->setVar('icon', 'repeat');
                        break;

                    default:
                        $view->setVar('icon', 'remove');
                        break;
                }
                $response->setBody($view->layout('error', $assets));
            }

            return $response;
        });

        return parent::handle($method, $uri);
    }

    /**
     * HMVC request in the application
     *
     * @param array $location location to run the request
     * @return mixed response
     */
    public function request($location)
    {
        $dispatcher = clone $this->getDi()->get('dispatcher');

        if (isset($location['handler'])) {
            $dispatcher->setHandler($location['handler']);
        } else {
            $dispatcher->setHandler('index');
        }

        if (isset($location['action'])) {
            $dispatcher->setAction($location['action']);
        } else {
            $dispatcher->setAction('index');
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
