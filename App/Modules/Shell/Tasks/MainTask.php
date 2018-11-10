<?php

namespace App\Modules\Shell\Tasks;

use App\Extensions\Task;
use Ice\Auth\Driver\Db as Auth;
use Ice\Db;
use Ice\I18n;
use Ice\Mvc\Url;
use Ice\Mvc\View;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Mvc\View\Engine\Sleet\Compiler;

/**
 * Main task.
 *
 * @category Task
 * @package  App
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class MainTask extends Task
{

    /**
     * Before execute action.
     *
     * @return void
     */
    public function before()
    {
        $config = $this->config;
        $this->di->i18n = new I18n($config->i18n->toArray());
        $this->di->auth = new Auth($config->auth->toArray());

        // Set the url service
        $this->di->set('url', function () use ($config) {
            $url = new Url();
            $url->setBaseUri($config->app->base_uri);
            $url->setStaticUri($config->app->static_uri);
            return $url;
        });

        // Set the db service
        $this->di->set('db', function () use ($config) {
            $db = new Db(
                $config->database->type,
                $config->database->host,
                $config->database->port,
                $config->database->name,
                $config->database->user,
                $config->database->password,
                $config->database->options->toArray()
            );

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
                'compile' => Compiler::IF_CHANGE
            ]);

            // Set template engines
            $view->setEngines([
                '.sleet' => $sleet,
                '.phtml' => 'Ice\Mvc\View\Engine\Php'
            ]);

            return $view;
        });
    }

    /**
     * Main Action - display info & available tasks.
     *
     * @return void
     */
    public function mainAction()
    {
        parent::info();
    }
}
