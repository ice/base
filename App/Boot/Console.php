<?php

namespace App\Boot;

use Ice\Auth\Driver\Db as Auth;
use Ice\Cli\Dispatcher;
use Ice\Cli\Router;
use Ice\Config\Env;
use Ice\Config\Ini;
use Ice\Dump;
use Ice\Db;
use Ice\I18n;
use Ice\Log\Driver\File as Logger;
use Ice\Mvc\Url;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Mvc\View\Engine\Sleet\Compiler;
use App\Lib\Email;

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
        $this->di->errors();

        // Load the config
        $this->di->config = new Ini(__ROOT__ . '/cfg/config.ini');
        $this->di->config->set('assets', new Ini(__ROOT__ . '/cfg/assets.ini'));

        // Set the environment
        $this->di->env = new Env(__ROOT__ . '/.env');
        $this->di->env = new Env(__ROOT__ . '/.env.' . $this->di->env->environment);

        // Register modules
        $this->setModules($this->di->config->{$this->di->config->modules->console->modules}->toArray());

        // Set dump
        if ($this->di->env->environment == "development") {
            $this->dump->setDetailed(true);
        }

        $this->di->i18n = new I18n($this->di->config->i18n->toArray());
        $this->di->auth = new Auth($this->di->config->auth->toArray());

        // Set the url service
        $this->di->set('url', function () {
            $url = new Url();
            $url->setBaseUri($this->di->config->app->base_uri);
            $url->setStaticUri($this->di->config->app->static_uri);
            return $url;
        });


        // Set services
        $this->di->dispatcher = new Dispatcher();

        $this->di->set('router', function () {
            $router = new Router();
            $router->setDefaultModule($this->di->config->modules->console->default);

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

            return $db;
        });

        // Set the view service
        $this->di->set('view', function () {
            $view = new View();
            $view->setViewsDir(__ROOT__ . '/views/');

            // Options for Sleet template engine
            $sleet = new Sleet($view, $this->di);
            $sleet->setOptions([
                'compileDir' => __ROOT__ . '/tmp/sleet/',
                'trimPath' => __ROOT__,
                'compile' => Compiler::IF_CHANGE
            ]);

            // Set template engines
            $view->setEngines([
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
        // Pretty exception
        $this->di->hook('exception.after.uncaught', function ($e, $di) {
            $message = $e->getMessage();
            $error = get_class($e) . '[' . $e->getCode() . ']: ' . $e->getMessage();
            $info = $e->getFile() . '[' . $e->getLine() . ']';
            $debug = "Trace: \n" . $e->getTraceAsString() . "\n";

            if ($di->env->error->log) {
                // Log error into the file
                $logger = new Logger(__ROOT__ . '/log/' . date('Ymd') . '.log');
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
                    $logger = new Logger(__ROOT__ . '/log/' . date('Ymd') . '.log');
                    $logger->error($email->ErrorInfo);
                }
            }

            if ($di->env->error->debug) {
                echo $di->dump->vars($error, $info, $debug);
            } else {
                if ($di->env->error->hide) {
                    $message = _t('somethingIsWrong');
                }

                echo $message;
            }
        });
    }
}
