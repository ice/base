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
            $this->response->setStatus(401);
            return $this->response;
        } elseif (!$this->auth->loggedIn('admin')) {
            // 403 Forbidden
            $this->response->setStatus(403);
            return $this->response;
        }

        parent::before();

        $this->assets
            // Add styles to assets
            ->add('css/frontend.css', $this->config->assets->frontend)
            // Add scripts to assets
            ->add('js/frontend.js', $this->config->assets->frontend);
    }
}
