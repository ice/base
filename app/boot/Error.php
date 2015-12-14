<?php

namespace App;

use App\Libraries\Email;
use Ice\Exception;
use Ice\Di;
use Ice\Dump;
use Ice\Log\Driver\File as Logger;
use Ice\Tag;

/**
 * Handle exception, do something with it depending on the environment
 *
 * @package     Ice/Base
 * @category    Errors
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

        $e = $previous ? $previous : $this;
        $error = get_class($e) . '[' . $e->getCode() . ']: ' . $e->getMessage();
        $info = $e->getFile() . '[' . $e->getLine() . ']';
        $debug = "Trace: \n" . $e->getTraceAsString() . "\n";

        // Get the error settings depending on environment
        $di = Di::fetch();

        if ($di->has("dump")) {
            $dump = $di->dump;
        } else {
            $dump = new Dump(true);
        }

        $err = $di->config->env->error;

        if ($err->debug) {
            // Display debug
            if (PHP_SAPI == 'cli') {
                var_dump($error, $info, $debug);
            } else {
                echo $dump->vars($error, $info, $debug);
            }
        } else {
            if (PHP_SAPI == 'cli') {
                echo $message;
            } else {
                if ($err->hide) {
                    $message = _t('somethingIsWrong');
                }

                // Load and display error view
                echo self::view($di, $code, $message);
            }
        }

        if ($err->log) {
            // Log error into the file
            $logger = new Logger(__ROOT__ . '/app/log/' . date('Ymd') . '.log');
            $logger->error($error);
            $logger->info($info);
            $logger->debug($debug);
        }

        if ($err->email) {
            // Send email to admin
            $log = $dump->vars($error, $info, $debug);

            $email = new Email();
            $email->prepare(_t('somethingIsWrong'), $di->config->app->admin, 'email/error', ['log' => $log]);

            if ($email->Send() !== true) {
                $logger = new Logger(__ROOT__ . '/app/log/' . date('Ymd') . '.log');
                $logger->error($email->ErrorInfo);
            }
        }
    }

    /**
     * Get the error view
     *
     * @param object di
     * @return string
     */
    public static function view($di, $code, $message)
    {
        $di->tag->setDocType(Tag::XHTML5);
        $di->tag->setTitle(_t('status :code', [':code' => $code]));
        $di->tag->appendTitle($di->config->app->name);

        if (!$di->tag->getMeta()) {
            // Add meta tags
            $di->tag
                ->addMeta(['charset' => 'utf-8'])
                ->addMeta(['IE=edge', 'http-equiv' => 'X-UA-Compatible'])
                ->addMeta(['width=device-width, initial-scale=1.0', 'viewport'])
                ->addMeta(['noindex, nofollow', 'robots']);
        }

        if (!$di->assets->getCss()) {
            // Add styles to assets
            $di->assets
                ->add('css/bootstrap.min.css', $di->config->assets->bootstrap)
                ->add('css/fonts.css', $di->config->assets->fonts)
                ->add('css/simple-line-icons.css', $di->config->assets->simpleLineIcons)
                ->add('css/frontend.css', $di->config->assets->frontend);
        }

        $di->assets->addCss(['content' => 'body { background: #f5f5f5 }']);

        $di->view->setVars([
            'code' => $code,
            'message' => $message
        ]);

        return $di->view->layout('error');
    }
}
