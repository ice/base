<?php

namespace App\Boot;

use Ice\Auth\Driver\Model as Auth;
use Ice\Config\Ini;
use Ice\Db;
use Ice\I18n;
use Ice\Mvc\App;
use Ice\Mvc\Router;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Http\Response\ResponseInterface;

/**
 * Base MVC application.
 *
 * @category Boot
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Base extends App
{

    /**
     * Meta description.
     *
     * @var string
     */
    public $description;

    /**
     * Meta keywords.
     *
     * @var string
     */
    public $keywords;

    /**
     * Initialize the application.
     *
     * @return object Base
     */
    public function initialize()
    {
        // Handle the errors by Error class
        $this->di->errors('App\Boot\Error');

        // Load the config
        $config = new Ini(__ROOT__ . '/App/cfg/config.ini');

        // Set environment settings
        $config->set('env', (new Ini(__ROOT__ . '/App/cfg/env.ini'))->{$config->app->env});
        $config->set('assets', new Ini(__ROOT__ . '/App/cfg/assets.ini'));
        $this->di->config = $config;

        // Register modules
        $this->setModules($config->{$config->modules->application->modules}->toArray());

        // Set services
        if ($config->app->env == "development") {
            $this->dump->setDetailed(true);
        }

        $this->di->crypt->setKey($config->crypt->key);
        $this->di->cookies->setSalt($config->cookie->salt);
        $this->di->i18n = new I18n($config->i18n->toArray());
        $this->di->auth = new Auth($config->auth->toArray());
        $this->di->url->setBaseUri($config->app->base_uri);
        $this->di->url->setStaticUri($config->app->static_uri);

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
                $config->database->password,
                $config->database->options->toArray()
            );

            if (strpos($config->database->type, "mongo") === false && $config->app->env == "development") {
                $db->getDriver()->getClient()->setAttribute(\Pdo::ATTR_ERRMODE, \Pdo::ERRMODE_EXCEPTION);
            }

            return $db;
        });

        // Set the view service
        $this->di->set('view', function () use ($config) {
            $view = new View();
            $view->setViewsDir(__ROOT__ . '/App/views/');

            // Options for Sleet template engine
            $sleet = new Sleet($view, $this->di);
            $sleet->setOptions([
                'compileDir' => __ROOT__ . '/App/tmp/sleet/',
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

        return $this;
    }

    /**
     * Overwrite response by display pretty view.
     *
     * @param string $method Request method
     * @param string $uri    Uri
     *
     * @return object response
     */
    public function handle($method = null, $uri = null): ResponseInterface
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
     * HMVC request in the application.
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
