<?php

namespace App\Extensions;

use Ice\Tag;

/**
 * Base controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class Controller extends \Ice\Mvc\Controller
{

    /**
     * Before execute action
     *
     * @return void
     */
    public function before()
    {
        // Set default title and description
        $this->tag->setDocType(Tag::XHTML5);
        $this->tag->setTitle($this->config->app->title);
        $this->app->description = $this->config->app->description;
        $this->app->keywords = $this->config->app->keywords;

        // Add styles to assets
        $this->assets->add('css/bootstrap.min.css', $this->config->assets->bootstrap);
        $this->assets->add('css/fonts.css');
        $this->assets->add('css/styles.css', $this->config->assets->styles);
        // Add scripts to assets
        $this->assets->add('js/jquery.min.js', $this->config->assets->jquery);
        $this->assets->add('js/bootstrap.min.js', $this->config->assets->bootstrap);
        $this->assets->add('js/plugins.js', $this->config->assets->plugins);
    }

    /**
     * Initialize the controller
     *
     * @return void
     */
    public function initialize()
    {
        $lifetime = $this->config->session->lifetime;

        // Check the session lifetime
        if ($this->session->has('last_active') && time() - $this->session->get('last_active') > $lifetime) {
            $this->session->destroy();
        }

        $this->session->set('last_active', time());

        // Set the language
        if ($this->session->has('lang')) {
            // Set the language from session
            $this->i18n->lang($this->session->get('lang'));
        } elseif ($this->cookies->has('lang')) {
            // Set the language from cookie
            $this->i18n->lang($this->cookies->get('lang'));
        }

        // Send langs to the view
        $this->view->setVars([
            // Translate langs before send
            'siteLangs' => array_map('_t', $this->config->i18n->langs->toArray())
        ]);
    }

    /**
     * After execute action
     *
     * @return void
     */
    public function after()
    {
        // Set final title and description
        $this->tag->setTitleSeparator(' | ');
        $this->tag->appendTitle($this->config->app->name);
        $this->app->description =
            mb_substr($this->filter->sanitize($this->app->description, 'string'), 0, 200, 'utf-8');
    }

    /**
     * Load this if something was not found
     */
    public function notFound()
    {
        $this->response->setStatus(404);
        $this->view->setMainView('error');
        $this->view->setContent(false);
    }

    /**
     * Display no access message
     */
    public function noAccess($redirect = null)
    {
        $this->message('No access', 'danger', 'flash/danger/forbidden', $redirect);
    }

    /**
     * Display some message
     */
    public function message($title, $type, $flash, $redirect = null)
    {
        $this->tag->setTitle(_t($title));
        $this->flash->{$type}(_t($flash));
        $this->view->setVar('title', _t($title));

        if ($redirect) {
            $this->view->setVar('redirect', $redirect);
        }

        $tmp = $this->view->partial('message');
        $this->view->setContent($tmp);
    }
}
