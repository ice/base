<?php

namespace App;

use Ice\Di;
use Ice\Dump;
use Ice\Exception;
use Ice\Log\Driver\File as Logger;
use App\Libraries\Email;

/**
 * Handle exception, do something with it depending on the environment
 *
 * @package     Ice/Base
 * @category    Errors
 * @version     1.0
 */
class Error extends Exception
{

    /**
     * Error constructor
     *
     * @param string $message
     * @param int $code
     * @param mixed $previous
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        // Make sure everything is assigned properly
        parent::__construct($message, (int) $code, $previous);

        $e = $this->getPrevious() ? $this->getPrevious() : $this;
        $error = get_class($e) . '[' . $e->getCode() . ']: ' . $e->getMessage();
        $info = $e->getFile() . '[' . $e->getLine() . ']';
        $debug = "Trace: \n" . $e->getTraceAsString() . "\n";

        // Get the error settings depending on environment
        $di = Di::fetch();
        $err = $di->config->env->error;

        if ($err->debug) {
            // Display debug
            if (PHP_SAPI == 'cli') {
                var_dump($error, $info, $debug);
            } else {
                if ($di->has("dump")) {
                    $dump = $di->dump;
                } else {
                    $dump = new Dump(true);
                }
                echo $dump->vars($error, $info, $debug);
            }
        } else {
            // Load and display error view
            if (PHP_SAPI == 'cli') {
                echo __('Something is wrong!');
            } else {
                // Load and display error view
                $view = $di->view;
                $view->setVar('message', $error);

                $assets['styles'] = [
                    $di->tag->link(['css/bootstrap.min.css?v=3.3.0']),
                    $di->tag->link(['css/fonts.css']),
                    $di->tag->link(['css/app.css'])
                ];

                echo $view->layout('error', $assets);
            }
        }

        if ($err->log) {
            // Log error into the file
            $logger = new Logger(__ROOT__ . '/app/var/log/' . date('Ymd') . '.log');
            $logger->error($error);
            $logger->info($info);
            $logger->debug($debug);
        }

        if ($err->email) {
            // Send email to admin
            if ($di->has("dump")) {
                $dump = $di->dump;
            } else {
                $dump = new Dump(true);
            }
            $log = $dump->vars($error, $info, $debug);

            $email = new Email();
            $email->prepare(__('Something is wrong!'), $di->config->app->admin, 'email/error', ['log' => $log]);

            if ($email->Send() !== true) {
                $logger = new Logger(__ROOT__ . '/app/var/log/' . date('Ymd') . '.log');
                $logger->error($email->ErrorInfo);
            }
        }
    }
}
