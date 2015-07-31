<?php

namespace App\Modules\Admin\Controllers;

use App\Extensions\Controller;

/**
 * Admin home controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class IndexController extends Controller
{

    /**
     * Before execute action
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

        // Set admin header
        $this->view->setVar('header', 'header_admin');
        
        parent::before();
    }

    /**
     * Display admin's home page
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('Admin panel'));
        $this->app->description = _t('Admin panel');
    }
}
