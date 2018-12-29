<?php

namespace App\Boot;

use Ice\Cli\Dispatcher;
use Ice\Cli\Router;
use Ice\Config\Ini;
use Ice\Dump;
use Ice\Log\Driver\File as Logger;
use App\Libraries\Email;

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
        $config = new Ini(__ROOT__ . '/config/config.ini');

        // Set environment settings
        $this->di->config = $config;

        // Register modules
        $console = $config->modules->console;
        $this->setModules($config->{$console->modules}->toArray());

        // Set dump
        $this->di->dump->setPlain(true);

        if (APP_ENV == "development") {
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

            if (getenv('LOGGER_ENABLED')) {
                // Log error into the file
                $logger = new Logger(__ROOT__ . '/var/log/' . date('Ymd') . '.log');
                $logger->error($error);
                $logger->info($info);
                $logger->debug($debug);
            }

            if (getenv('MAILER_ENABLED') and getenv('ERROR_NOTIFICATIONS_ENABLED')) {
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

                if ($email->send() !== true) {
                    $logger = new Logger(__ROOT__ . '/var/log/' . date('Ymd') . '.log');
                    $logger->error($message);
                }
            }

            if (getenv('DEBUG_MODE')) {
                echo $di->dump->vars($error, $info, $debug);
            } else {
                echo _t('somethingIsWrong');
            }
        });
    }
}
