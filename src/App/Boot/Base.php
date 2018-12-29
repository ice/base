<?php

namespace App\Boot;

use Ice\Auth\Driver\Db as Auth;
use Ice\Config\Ini;
use Ice\Db;
use Ice\Di;
use Ice\Http\Response;
use Ice\I18n;
use Ice\Mvc\App;
use Ice\Mvc\Router;
use Ice\Log\Driver\File as Logger;
use Ice\Tag;
use App\Libraries\Email;
use App\Libraries\Markdown;
use Ice\Mvc\View\Engine as ViewEngine;


/**
 * Base MVC application.
 *
 * @property Di $di
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
        $di = $this->configureContainer();

        // Load the config
        if (!defined('APP_ENV')) {
            define('APP_ENV', $di->config->app->env);
        }

        // Register modules
        $this->setModules($this->loadModules());

        // Set dump
        if (APP_ENV == "development") {
            $this->dump->setDetailed(true);
        }

        // Configure services
        $di->crypt->setKey($di->config->crypt->key);
        $di->cookies->setSalt($di->config->cookie->salt);
        $di->i18n = new I18n($di->config->i18n->toArray());
        $di->auth = new Auth($di->config->auth->toArray());
        $di->url->setBaseUri($this->getBaseUri());
        $di->url->setStaticUri($this->getStaticUri());

        // Set the assets service
        /*$di->assets->setOptions([
            'source' => __ROOT__ . '/public/',
            'target' => 'min/',
            'minify' => getenv('MINIFY_ASSETS_MODE')
        ]);*/

        // Set the dispatcher service
        $di->dispatcher->setSilent(!getenv('DISPATCHER_DEBUG'));

        // Set the router service
        $di->set('router', function () use($di) {
            $router = new Router();
            $defaultModule = $di->config->modules->application->default;
            if (!$defaultModule) {
                $defaultModule = 'front';
            }
            $router->setDefaultModule($defaultModule);
            $router->setSilent(!getenv('ROUTER_DEBUG'));
            $router->setRoutes(Routes::universal());

            return $router;
        });

        // Set the db service
        $di->set('db', function() use($di){
            if (!$di->has('config')) {
                throw new \LogicException("The 'config' service is not registered.");
            }

            $config = $di->get('config');
            if (!isset($config->database)) {
                throw new \LogicException("The 'database' configuration is missing.");
            }

            // To use another database you may need to add your own environment variables
            $db = new Db(
                'mongodb',
                (string) getenv('MONGODB_HOST'),
                (int)    getenv('MONGODB_PORT'),
                (string) getenv('MONGODB_DATABASE'),
                (string) getenv('MONGODB_USERNAME'),
                (string) getenv('MONGODB_PASSWORD'),
                [
                    'authMechanism' => getenv('MONGODB_AUTH_MECHANISM')
                ]
            );

            if (APP_ENV == "development") {
                $dbClient = $db->getDriver()->getClient();
                if ($dbClient instanceof \PDO) {
                    $dbClient->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                }
            }

            return $db;
        });

        // Set the view service
        $di->set('view', function() use($di) {
            $view = new \Ice\Mvc\View();
            $view->setViewsDir(__ROOT__ . '/templates/');
            $view->setLayoutsDir(__ROOT__ . '/templates/layouts/');
            $view->setPartialsDir(__ROOT__ . '/templates/partials/');

            $templateCacheDir = __ROOT__ . '/var/cache/' . APP_ENV . '/sleet/';
            if (!is_dir($templateCacheDir)) {
                mkdir($templateCacheDir, 0755, true);
            }

            // Options for Sleet template engine
            $sleet = new ViewEngine\Sleet($view, $di);
            $sleet->setOptions([
                'compileDir' => $templateCacheDir,
                'trimPath' => __ROOT__ . '/src',
                'compile' => (int) getenv('SLEET_COMPILER_MODE') ?: 0
            ]);

            // Set template engines
            $view->setEngines([
                '.sleet' => $sleet,
                '.md' => Markdown::class,
                '.phtml' => ViewEngine\Php::class
            ]);

            return $view;
        });

        // Register hooks
        $this->registerHooks();

        return $this;
    }

    /**
     * @param Response $response
     */
    public function postHandleHandler($response)
    {
        // Display pretty view for some response codes
        if (!$response->isInformational() && !$response->isSuccessful() && !$response->isRedirect()) {
            $code = $response->getStatus();

            // Add meta tags
            $this->di->tag
                ->setDocType(Tag::XHTML5)
                ->setTitle(_t('status :code', [':code' => $response->getStatus()]))
                ->appendTitle($this->di->config->app->name)
                ->setMeta([])
                ->addMeta(['charset' => 'utf-8'])
                ->addMeta(['noindex, nofollow', 'robots'])
                ->addMeta(['initial-scale=1, minimum-scale=1, width=device-width', 'viewport']);

            // Add styles to assets
            $this->di->assets
                ->setCollections([])
                ->add('css/response.css');

            // Restore default view settings
            $this->di->view
                ->setViewsDir(__ROOT__ . '/templates/')
                ->setPartialsDir('partials/')
                ->setLayoutsDir('layouts/')
                ->setFile('partials/status')
                ->setContent($this->di->view->render());

            $response->setBody($this->di->view->layout('minimal'));
        }
    }

    /**
     * @param \Exception $e
     * @param Di $di
     */
    public function uncaughtExceptionHandler($e, $di)
    {
        $error = get_class($e) . '[' . $e->getCode() . ']: ' . $e->getMessage();
        $info = $e->getFile() . '[' . $e->getLine() . ']';

        if (getenv('LOGGER_ENABLED')) {
            // Log error into the file
            $logDir = __ROOT__ . '/var/log/' . APP_ENV . '/';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0775, true);
            }
            $logger = new Logger($logDir . date('Ymd') . '.log');
            $logger->error($error);
            $logger->info($info);
            if (getenv('DEBUG_MODE')) {
                $logger->debug("Trace: \n" . $e->getTraceAsString() . "\n");
            }
        }

        if (getenv('MAILER_ENABLED') and getenv('ERROR_NOTIFICATIONS_ENABLED')) {
            // Send email to admin
            $log = $di->dump->vars($error, $info, "Trace: \n" . $e->getTraceAsString() . "\n");

            if ($di->has("request")) {
                $log .= $di->dump->one($di->request->getData(), '_REQUEST');
                $log .= $di->dump->one($di->request->getServer()->getData(), '_SERVER');
                $log .= $di->dump->one($di->request->getPost()->getData(), '_POST');
                $log .= $di->dump->one($di->request->getQuery()->getData(), '_GET');
            }

            $email = new Email();
            $email->prepare(_t('somethingIsWrong'), $di->config->app->admin, 'email/error', ['log' => $log]);

            if ($email->send() !== true) {
                $logger = new Logger(__ROOT__ . '/var/log/' . date('Ymd') . '.log');
                $logger->error($e->getMessage());
            }
        }

        $response = $di->get('response');
        $response->setStatus(500);

        if (PHP_SAPI == 'cli') {
            $response->setBody($e->getMessage());
        } elseif (!getenv('DEBUG_MODE')) {
            $di->applyHook("app.after.handle", [$response]);
        } else {
            // Add meta tags
            $di->tag
                ->setDocType(Tag::XHTML5)
                ->setTitle(_t('status :code', [':code' => $e->getCode()]))
                ->appendTitle($di->config->app->name)
                ->setMeta([])
                ->addMeta(['charset' => 'utf-8'])
                ->addMeta(['IE=edge', 'http-equiv' => 'X-UA-Compatible'])
                ->addMeta(['width=device-width, initial-scale=1.0', 'viewport'])
                ->addMeta(['noindex, nofollow', 'robots']);

            // Add styles to assets
            $di->assets
                ->setCollections([])
                ->add('css/exception.css')
                ->add('css/highlight/tomorrow.min.css', $this->config->assets->highlight)
                ->add('js/jquery.min.js', $this->config->assets->jquery)
                ->add('js/plugins/highlight.min.js', $this->config->assets->highlight)
                ->add('js/exception.js');

            // Restore default view settings
            $di->view
                ->setViewsDir(__ROOT__ . '/templates/')
                ->setPartialsDir('partials/')
                ->setLayoutsDir('layouts/')
                ->setFile('partials/exception')
                ->setVar('e', $e)
                ->setContent($di->view->render());

            $response->setBody($di->view->layout('minimal'));
        }
    }

    /**
     * Register hooks in the di.
     *
     * @return void
     */
    public function registerHooks()
    {
        // Response code
        $this->di->hook('app.after.handle', [$this, 'postHandleHandler']);

        // Pretty exception
        $this->di->hook('exception.after.uncaught', [$this, 'uncaughtExceptionHandler']);
    }

    /**
     * @return string
     */
    private function getBaseUri()
    {
        $baseUri = $this->di->config->app->base_uri;
        if (empty($baseUri)) {
            throw new \RuntimeException("The base uri is not set.");
        }

        return $baseUri;
    }

    /**
     * @return string
     */
    private function getStaticUri()
    {
        $staticUrl = $this->di->config->app->static_uri;
        if (empty($staticUrl)) {
            throw new \RuntimeException("The static uri is not set.");
        }

        return $staticUrl;
    }

    /**
     * @return string[]
     */
    private function loadModules()
    {
        $modules = $this->di->config->{$this->di->config->modules->application->modules}->toArray();
        if (empty($modules)) {
            throw new \RuntimeException("There are no registered modules.");
        }

        return $modules;
    }

    private function configureContainer()
    {
        // Handle the errors by Error class
        $this->di->errors();
        $this->di->config = new Ini(__ROOT__ . '/config/config.ini');
        $this->di->config->set('assets', new Ini(__ROOT__ . '/config/assets.ini'));

        return $this->di;
    }
}
