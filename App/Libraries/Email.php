<?php

namespace App\Libraries;

use Ice\Di;
use Ice\Config;
use Ice\Log\Driver\File as Logger;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Mvc\View\Engine\Sleet\Compiler;
use Crossjoin\PreMailer\HtmlString as Premailer;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Email Library.
 *
 * @category Libraries
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 * @uses     PHPMailer
 */
class Email extends PHPMailer
{

    private $di;
    private $premailer;

    /**
     * Email constructor
     *
     * @return object PHPMailer
     */
    public function __construct()
    {
        $email = new PHPMailer();
        $this->di = Di::fetch();

        // Load email config from config.ini
        if ($config = $this->di->config->email) {
            foreach ($config as $key => $value) {
                if ($key == 'ssl' && $value instanceof Config) {
                    $this->SMTPOptions = [
                        'ssl' => $value->toArray()
                    ];
                } else {
                    $this->$key = $value;
                }
            }
        }

        return $email;
    }

    /**
     * Get email template and load view with params
     *
     * @param string $name   View name to load
     * @param array  $params Params to send to the view
     *
     * @return string
     */
    public function getTemplate($name, $params = [])
    {
        // Prepare view service
        $view = new View();
        $view->setViewsDir(__ROOT__ . '/App/views/');
        $view->setMainView('email');

        // Options for Sleet template engine
        $sleet = new Sleet($view, $this->di);
        $sleet->setOptions([
            'compileDir' => __ROOT__ . '/App/tmp/sleet/',
            'trimPath' => __ROOT__,
            'compile' => Compiler::IF_CHANGE
        ]);

        // Set template engines
        $view->setEngines([
            '.sleet' => $sleet,
            '.phtml' => 'Ice\Mvc\View\Engine\Php'
        ]);

        $view->setContent($view->render($name, array_merge(['__ROOT__' => __ROOT__], $params)));
        return $this->getInline($view->layout());
    }

    /**
     * Convert our HTML email using Premailer
     *
     * @param string $source Html code
     *
     * @return string
     */
    public function getInline($source)
    {
        $this->premailer = new Premailer($source);
        $preMailer = $this->premailer;
        $this->premailer->setOption($preMailer::OPTION_STYLE_TAG, $preMailer::OPTION_STYLE_TAG_REMOVE);
        $this->premailer->setOption($preMailer::OPTION_HTML_CLASSES, $preMailer::OPTION_HTML_CLASSES_REMOVE);

        return $this->premailer->getHtml();
    }

    /**
     * Prepare email - set title, recipment and body
     *
     * @param string $subject Email subject
     * @param string $to      Email recipment
     * @param string $view    View name to load
     * @param array  $params  Params to send to the view

     * @return string
     */
    public function prepare($subject, $to, $view, $params = [])
    {
        if ($this->di->config->app->env == 'development') {
            $to = $this->di->config->app->admin;
        }
        $this->Subject = $subject;
        $this->AddAddress($to);

        // Load email content from template and view
        $body = $this->getTemplate($view, $params);
        $this->MsgHTML($body);

        return $body;
    }

    /**
     * Send or log an email depending on environment
     *
     * @return boolean
     */
    public function send()
    {
        if ($this->di->config->env->email) {
            return parent::send();
        } else {
            $this->preSend();
            // Log email into the file
            $logger = new Logger(__ROOT__ . '/App/log/' . date('Ymd') . '.log');
            $logger->info('Subject: ' . $this->Subject . '; To: ' . json_encode($this->to));
            $logger->error($this->ErrorInfo);
            $logger->debug($this->Body);
            return true;
        }
    }

    /**
     * Get error info
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->ErrorInfo;
    }
}
