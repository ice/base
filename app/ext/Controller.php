<?php

namespace App\Extensions;

use Ice\Arr;
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
        $this->app->description = $this->config->app->description;
        $this->app->keywords = $this->config->app->keywords;

        $this->tag->setDocType(Tag::XHTML5);
        $this->tag->setTitle($this->config->app->title);

        // Set layout' class container
        $this->app->layout = new Arr([]);

        // Add meta tags
        $this->tag
            ->addMeta(['charset' => 'utf-8'])
            ->addMeta(['IE=edge', 'http-equiv' => 'X-UA-Compatible'])
            ->addMeta(['width=device-width, initial-scale=1.0', 'viewport'])
            ->addMeta(['index, follow', 'robots']);
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
        $this->app->description =
            mb_substr($this->filter->sanitize($this->app->description, 'string'), 0, 200, 'utf-8');

        $this->tag->setTitleSeparator(' | ');
        $this->tag->appendTitle($this->config->app->name);

        // Add meta tags
        $this->tag
            ->addMeta([$this->app->description, 'description', 'property' => 'og:description'])
            ->addMeta([$this->app->keywords, 'keywords']);
    }

    /**
     * Load this if something was not found
     */
    public function notFound()
    {
        return $this->responseCode(404);
    }
    
    public function responseCode($code = 200)
    {
        $this->app->setAutoRender(false);
        $this->response->setStatus($code);
        return $this->response;
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
