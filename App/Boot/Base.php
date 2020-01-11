<?php

namespace App\Boot;

use Ice\Auth\Driver\Db as Auth;
use Ice\Config\Env;
use Ice\Config\Ini;
use Ice\Db;
use Ice\I18n;
use Ice\Mvc\App;
use Ice\Mvc\Router;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Http\Response\ResponseInterface;
use Ice\Log\Driver\File as Logger;
use Ice\Tag;
use Ice\Assets;
use App\Lib\Email;

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
        $this->di->errors();

        // Load the config
        $this->di->config = new Ini(__ROOT__ . '/App/cfg/config.ini');
        $this->di->config->set('assets', new Ini(__ROOT__ . '/App/cfg/assets.ini'));

        // Set the environment
        $this->di->env = new Env(__ROOT__ . '/App/.env');
        $this->di->env = new Env(__ROOT__ . '/App/.env.' . $this->di->env->environment);

        // Register modules
        $this->setModules($this->di->config->{$this->di->config->modules->application->modules}->toArray());

        // Set dump
        if ($this->di->env->environment == "development") {
            $this->dump->setDetailed(true);
        }

        // Configure services
        $this->di->crypt->setKey($this->di->config->crypt->key);
        $this->di->cookies->setSalt($this->di->config->cookie->salt);
        $this->di->i18n = new I18n($this->di->config->i18n->toArray());
        $this->di->auth = new Auth($this->di->config->auth->toArray());
        $this->di->url->setBaseUri($this->di->config->app->base_uri);
        $this->di->url->setStaticUri($this->di->config->app->static_uri);

        // Set the assets service
        $this->di->assets->setOptions([
            'source' => __ROOT__ . '/public/',
            'target' => 'min/',
            'minify' => $this->di->env->assets->minify
        ]);

        // Set the dispatcher service
        $this->di->dispatcher->setSilent($this->di->env->silent->dispatcher);

        // Set the router service
        $this->di->set('router', function () {
            $router = new Router();
            $router->setDefaultModule($this->di->config->modules->application->default);
            $router->setSilent($this->di->env->silent->router);
            $router->setRoutes((new Routes())->universal());
            return $router;
        });

        // Set the db service
        $this->di->set('db', function () {
            $db = new Db(
                $this->di->config->database->type,
                $this->di->config->database->host,
                $this->di->config->database->port,
                $this->di->config->database->name,
                $this->di->config->database->user,
                $this->di->config->database->password,
                (array) $this->di->config->database->options
            );

            if ($this->di->config->app->env == "development" && $this->di->config->database->type != "mongodb") {
                $db->getDriver()->getClient()->setAttribute(\Pdo::ATTR_ERRMODE, \Pdo::ERRMODE_EXCEPTION);
            }

            return $db;
        });

        // Set the view service
        $this->di->set('view', function () {
            $view = new View();
            $view->setViewsDir(__ROOT__ . '/App/views/');

            // Options for Sleet template engine
            $sleet = new Sleet($view, $this->di);
            $sleet->setOptions([
                'compileDir' => __ROOT__ . '/App/tmp/sleet/',
                'trimPath' => __ROOT__,
                'compile' => $this->di->env->sleet->compile
            ]);

            // Set template engines
            $view->setEngines([
                '.md' => 'App\Libraries\Markdown',
                '.sleet' => $sleet,
                '.phtml' => 'Ice\Mvc\View\Engine\Php'
            ]);

            return $view;
        });

        // Register hooks
        $this->registerHooks();

        return $this;
    }

    /**
     * Register hooks in the di.
     *
     * @return void
     */
    public function registerHooks()
    {
        // Response code
        $this->di->hook('app.after.handle', function ($response) {
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
                    ->addMeta(['initial-scale=1, minimum-scale=1, width=device-width', 'viewport']);

                // Add styles to assets
                $this->di->assets
                    ->setCollections([])
                    ->add('css/response.css');

                // Restore default view settings
                $this->di->view
                    ->setViewsDir(__ROOT__ . '/App/views/')
                    ->setPartialsDir('partials/')
                    ->setLayoutsDir('layouts/')
                    ->setFile('partials/status')
                    ->setContent($this->di->view->render());

                $response->setBody($this->di->view->layout('minimal'));
            }
        });

        // Pretty exception
        $this->di->hook('exception.after.uncaught', function ($e, $di) {
            $error = get_class($e) . '[' . $e->getCode() . ']: ' . $e->getMessage();
            $info = $e->getFile() . '[' . $e->getLine() . ']';
            $debug = "Trace: \n" . $e->getTraceAsString() . "\n";

            if ($di->env->error->log) {
                // Log error into the file
                $logger = new Logger(__ROOT__ . '/App/log/' . date('Ymd') . '.log');
                $logger->error($error);
                $logger->info($info);
                $logger->debug($debug);
            }

            if ($di->env->error->email) {
                // Send email to admin
                $log = $di->dump->vars($error, $info, $debug);

                if ($di->has("request")) {
                    $log .= $di->dump->one($di->request->getData(), '_REQUEST');
                    $log .= $di->dump->one($di->request->getServer()->getData(), '_SERVER');
                    $log .= $di->dump->one($di->request->getPost()->getData(), '_POST');
                    $log .= $di->dump->one($di->request->getQuery()->getData(), '_GET');
                }

                $email = new Email();
                $email->prepare(_t('somethingIsWrong'), $di->config->app->admin, 'email/error', ['log' => $log]);

                if ($email->Send() !== true) {
                    $logger = new Logger(__ROOT__ . '/App/log/' . date('Ymd') . '.log');
                    $logger->error($email->ErrorInfo);
                }
            }

            $response = $di->get('response');
            $response->setStatus(500);

            if (PHP_SAPI == 'cli') {
                $response->setBody($e->getMessage());
            } elseif ($di->env->error->hide) {
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
                    ->setViewsDir(__ROOT__ . '/App/views/')
                    ->setPartialsDir('partials/')
                    ->setLayoutsDir('layouts/')
                    ->setFile('partials/exception')
                    ->setVar('e', $e)
                    ->setContent($di->view->render());

                $response->setBody($di->view->layout('minimal'));
            }
        });
    }
}
