<?php

namespace App\Extensions;

/**
 * Admin controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class Admin extends Controller
{

    /**
     * Before execute action
     *
     * @return void
     */
    public function before()
    {
        // Check privileges
        if (!$this->auth->loggedIn()) {
            // 401 Unauthorized
            return parent::responseCode(401);
        } elseif (!$this->auth->loggedIn('admin')) {
            // 403 Forbidden
            return parent::responseCode(403);
        }

        parent::before();

        $versions = $this->config->assets;
        $this->assets
            // Add styles to assets
            ->add('css/bootstrap.min.css', $versions->bootstrap)
            ->add('css/fonts.css', $versions->fonts)
            ->add('css/simple-line-icons.css', $versions->simplelineicons)
            ->add('css/backend.css', $versions->backend)
            ->add('css/tether.min.css', $versions->tether)
            // Add scripts to assets
            ->add('js/jquery.min.js', $versions->jquery)
            ->add('js/plugins/tether.min.js', $versions->tether)
            ->add('js/bootstrap.min.js', $versions->bootstrap)
            ->add('js/backend.js', $versions->backend);
    }
}
