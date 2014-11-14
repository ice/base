<?php

namespace App\Libraries;

use Ice\Di;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Mvc\View\Engine\Sleet\Compiler;
use PHPMailer;

/**
 * Email Library
 *
 * @package     Ice/Base
 * @category    Library
 * @uses        PHPMailer
 */
class Email extends PHPMailer
{

    /**
     * Email constructor
     *
     * @return object PHPMailer
     */
    public function __construct()
    {
        $email = new \PHPMailer();

        // Load email config from config.ini
        if ($config = Di::fetch()->config->email) {
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        }

        return $email;
    }

    /**
     * Get email template and load view with params
     *
     * @param string $name View name to load
     * @param array $params Params to send to the view
     * @return string
     */
    public function getTemplate($name, $params = [])
    {
        // Prepare view service
        $view = new View();
        $view->setViewsDir(__ROOT__ . '/app/var/views/');
        $view->setMainView('email');

        // Options for Sleet template engine
        $sleet = new Sleet($view, Di::fetch());
        $sleet->setOptions([
            'compileDir' => __ROOT__ . '/app/var/tmp/sleet/',
            'trimPath' => __ROOT__,
            'compile' => Compiler::IF_CHANGE
        ]);

        // Set template engines
        $view->setEngines([
            '.sleet' => $sleet,
            '.phtml' => 'Ice\Mvc\View\Engine\Php'
        ]);

        $view->setContent($view->render($name, $params));
        return $view->layout();
    }

    /**
     * Prepare email - set title, recipment and body
     *
     * @param string $subject Email subject
     * @param string $to Email recipment
     * @param string $view Vview name to load
     * @param array $params Params to send to the view
     * @return string
     */
    public function prepare($subject, $to, $view, $params = [])
    {
        $this->Subject = $subject;
        $this->AddAddress($to);

        // Load email content from template and view
        $body = $this->getTemplate($view, $params);
        $this->MsgHTML($body);

        return $body;
    }
}
