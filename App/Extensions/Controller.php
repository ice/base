<?php

namespace App\Extensions;

use Ice\Arr;
use Ice\Tag;

/**
 * Base controller.
 *
 * @category Extensions
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Controller extends \Ice\Mvc\Controller
{

    /**
     * Meta description.
     *
     * @var string
     */
    public $description;

    /**
     * Meta keywords.
     *
     * @var string
     */
    public $keywords;

    /**
     * Before execute action.
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
        $this->app->layout = $this->config->layout;

        // Add meta tags
        $this->tag
            ->addMeta(['charset' => 'utf-8'])
            ->addMeta(['width=device-width, initial-scale=1, shrink-to-fit=no', 'viewport'])
            ->addMeta(['index, follow', 'robots'])
            ->addMeta([$this->config->key->google_validate, 'google-site-verification'])
            ->addMeta([$this->config->key->ms_validate, 'msvalidate.01']);

        $this->assets
            // Add styles to assets
            ->add('css/bootstrap.min.css', $this->config->assets->bootstrap)
            ->add('css/fonts.css', $this->config->assets->fonts)
            ->add('css/simple-line-icons.css', $this->config->assets->simplelineicons)

            // Add scripts to assets
            ->add('js/jquery.min.js', $this->config->assets->jquery)
            ->add('js/plugins/popper.min.js', $this->config->assets->popper)
            ->add('js/bootstrap.min.js', $this->config->assets->bootstrap);

        $this->jsTranslations = new Arr([]);
        $this->layout = new Arr([]);
    }

    /**
     * Initialize the controller.
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
        $this->setLanguage();

        // Send langs to the view
        $this->view->setVars([
            // Translate langs before send
            'siteLangs' => array_map('_t', $this->config->i18n->langs->toArray())
        ]);
    }

    /**
     * Add translations to js.
     *
     * @param array $translations Keys to translate
     *
     * @return void
     */
    public function addJsTranslations($translations)
    {
        foreach ($translations as $key) {
            $this->jsTranslations->set($key, _t($key));
        }
    }

    /**
     * Set the language.
     *
     * @return void
     */
    public function setLanguage()
    {
        // Set the language
        if ($this->session->has('lang')) {
            // Set the language from session
            $this->i18n->lang($this->session->get('lang'));
        } elseif ($this->auth->loggedIn() && $this->auth->getUser()->language) {
            // Set user's language
            $this->i18n->lang($this->auth->getUser()->language);
        } elseif ($this->cookies->has('lang')) {
            // Set the language from cookie
            $this->i18n->lang($this->cookies->get('lang'));
        }
    }

    /**
     * After execute action.
     *
     * @return void
     */
    public function after()
    {
        // Set final title and description
        $description = mb_substr($this->filter->sanitize($this->app->description, 'string'), 0, 200, 'utf-8');
        $this->app->description = $description;

        $this->tag->setTitleSeparator(' | ');
        $this->tag->appendTitle($this->config->app->name);

        // Add meta tags
        $this->tag
            ->addMeta([$this->app->description, 'description', 'property' => 'og:description'])
            ->addMeta([$this->app->keywords, 'keywords']);

        // Add js translations
        $translations = json_encode($this->jsTranslations->getData());
        $this->assets
            ->addJs(['content' => <<<JS
                window.paceOptions = {
                    document: true,
                    eventLag: true,
                    restartOnPushState: true,
                    restartOnRequestAfter: true,
                    ajax: {
                        trackMethods: ['POST','GET']
                    }
                };
JS
            ], '1.0.0', 'beforeJs')
            ->addJs(['content' => <<<JS
                var lang = {$translations};
JS
            ], '1.0.0', 'beforeJs')
            // Google analytics
            ->addJs(['content' => <<<JS
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
                ga('create', "{$this->config->key->analytics}", 'auto');
                ga('send', 'pageview');
JS
            ], '1.0.0', 'beforeJs');
    }

    /**
     * Load this if something was not found.
     *
     * @return string
     */
    public function notFound()
    {
        return $this->responseCode(404);
    }

    /**
     * Set status and return response.
     *
     * @param integer $code Response code
     *
     * @return string
     */
    public function responseCode($code = 200)
    {
        $this->app->setAutoRender(false);
        $this->response->setStatus($code);

        return $this->response;
    }

    /**
     * Display no access message.
     *
     * @param mixed   $redirect Uri
     * @param integer $timeout  In seconds
     *
     * @return void
     */
    public function noAccess($redirect = null, $timeout = null)
    {
        $this->message('No access', 'danger', 'flash/danger/forbidden', $redirect, $timeout);
    }

    /**
     * Display some message.
     *
     * @param string  $title    Title
     * @param string  $type     Flash type
     * @param string  $flash    Flash message or i18n key
     * @param mixed   $redirect Redirect URI
     * @param integer $timeout  In seconds
     *
     * @return void
     */
    public function message($title, $type, $flash, $redirect = null, $timeout = null)
    {
        $this->tag->setTitle(_t($title));
        $this->flash->{$type}(_t($flash));
        $this->view->setVar('title', _t($title));

        if ($redirect) {
            $this->view->setVar('redirect', $redirect);
        }

        if ($timeout) {
            $this->view->setVar('timeout', $timeout);
        }

        $tmp = $this->view->partial('message');
        $this->view->setContent($tmp);
    }
}
