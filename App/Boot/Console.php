<?php

namespace App\Boot;

use Ice\Cli\Dispatcher;
use Ice\Cli\Router;
use Ice\Config\Ini;
use Ice\Dump;
use Ice\Log\Driver\File as Logger;
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
        $config = new Ini(__ROOT__ . '/App/cfg/config.ini');

        // Set environment settings
        $config->set('env', (new Ini(__ROOT__ . '/App/cfg/env.ini'))->{$config->app->env});
        $this->di->config = $config;

        // Register modules
        $console = $config->modules->console;
        $this->setModules($config->{$console->modules}->toArray());

        // Set dump
        $this->di->dump->setPlain(true);

        if ($this->di->env->environment == "development") {
            $this->dump->setDetailed(true);
        }

        // Register hooks
        $this->registerHooks();

        // Set services
        $this->di->dispatcher = new Dispatcher();

        $this->di->set('router', function () use ($config) {
            $router = new Router();
            $router->setDefaultModule($config->modules->console->default);

            return $router;
        });

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
