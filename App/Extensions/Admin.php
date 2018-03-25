<?php

namespace App\Extensions;

/**
 * Admin controller.
 *
 * @category Extensions
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Admin extends Controller
{

    /**
     * Before execute action.
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
            ->add('css/sb-admin.min.css', $this->config->assets->sbadmin)
            ->add('css/admin.css', $this->config->assets->admin)
            ->add('js/sb-admin.min.js', $this->config->assets->sbadmin)
            ->add('js/admin.js', $this->config->assets->admin);

            $this->app->layout->replace([
                'body' => 'fixed-nav sticky-footer bg-dark',
                'main' => 'content-wrapper',
            ]);
    }
}
