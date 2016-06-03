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
        $debug = "Trace: \n" . $this->getFullTraceAsString($e) . "\n";

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

        // Clear meta tags and assets
        $di->tag->setMeta([]);
        $di->assets->setCollections([]);

        // Add meta tags
        $di->tag
            ->addMeta(['charset' => 'utf-8'])
            ->addMeta(['IE=edge', 'http-equiv' => 'X-UA-Compatible'])
            ->addMeta(['width=device-width, initial-scale=1.0', 'viewport'])
            ->addMeta(['noindex, nofollow', 'robots']);
        
        // Add styles to assets
        $di->assets
            ->add('css/material.min.css', $di->config->assets->material)
            ->add('css/fonts.css', $di->config->assets->fonts)
            ->add('css/simple-line-icons.css', $di->config->assets->simpleLineIcons)
            ->add(['content' => 'content { padding: 40px; text-align: center }', 'type' => 'text/css']);

        // Restore default view settings
        $di->view->setViewsDir(__ROOT__ . '/app/views/');
        $di->view->setPartialsDir('partials/');
        $di->view->setLayoutsDir('layouts/');
        $di->view->setFile('partials/error');

        if ($di->response->isServerError()) {
            $bg = 'mdl-color--red';
        } elseif ($di->response->isClientError()) {
            $bg = 'mdl-color--blue';
        } elseif ($di->response->isRedirection()) {
            $bg = 'mdl-color--orange';
        } else {
            $bg = 'mdl-color--amber';
        }

        $di->view->setVars([
            'bg' => $bg,
            'title' => _t('status :code', [':code' => $code]),
            'content' => $message,
            'actions' => $di->tag->linkTo([
                null,
                '<i class="icon-home"></i> ' . _t('home'),
                'class' => 'mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect'
            ])
        ]);
        $di->view->setContent($di->view->render());


        return $di->view->layout('minimal');
    }
}
