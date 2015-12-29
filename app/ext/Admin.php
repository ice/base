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

        $this->assets
            // Add styles to assets
            ->add('css/frontend.css', $this->config->assets->frontend)
            // Add scripts to assets
            ->add('js/frontend.js', $this->config->assets->frontend);
    }
}
