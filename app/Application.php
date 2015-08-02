<?php

namespace App;

use Ice\Assets;
use Ice\Auth\Driver\Model as Auth;
use Ice\Config\Ini;
use Ice\Config\Json;
use Ice\Cookies;
use Ice\Crypt;
use Ice\Db;
use Ice\Di;
use Ice\Dump;
use Ice\Filter;
use Ice\Flash;
use Ice\Http\Request;
use Ice\Http\Response;
use Ice\I18n;
use Ice\Loader;
use Ice\Mvc\App;
use Ice\Mvc\Dispatcher;
use Ice\Mvc\Router;
use Ice\Mvc\Url;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Mvc\View\Engine\Sleet\Compiler;
use Ice\Session;
use Ice\Tag;

/**
 * Mvc application
 *
 * @package     Ice/Base
 * @category    Bootstrap
 */
class Application extends App
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
     * App constructor
     *
     * @param Di $di
     */
    public function __construct(Di $di)
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
        $config = new Ini(__ROOT__ . '/app/cfg/config.ini');

        // Set environment settings
        $config->set('env', (new Ini(__ROOT__ . '/app/cfg/env.ini'))->{$config->app->env});
        $config->set('assets', new Json(__ROOT__ . '/app/cfg/assets.json'));
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

        // Set the assets service
        $this->di->set('assets', function () use ($config) {
            $assets = new Assets();
            $assets->setOptions([
                'source' => __ROOT__ . '/public/',
                'target' => 'min/',
                'minify' => $config->env->assets->minify
            ]);
            return $assets;
        });

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
            $driver = new Db\Driver\Pdo(
                'mysql:host=' . $config->database->host . ';port=3306;dbname=' . $config->database->dbname,
                $config->database->username,
                $config->database->password
            );
            //$driver->getClient()->setAttribute(\Pdo::ATTR_ERRMODE, \Pdo::ERRMODE_EXCEPTION);
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
