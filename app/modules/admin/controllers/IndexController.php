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
     * Check privileges
     */
    public function initialize()
    {
        if (!$this->auth->loggedIn()) {
            // 401 Unauthorized
            $this->response->setStatus(401);
            return $this->response;
        } elseif (!$this->auth->loggedIn('admin')) {
            // 403 Forbidden
            $this->response->setStatus(403);
            return $this->response;
        }

        parent::initialize();

        // Set admin header
        $this->view->setVar('header', 'header_admin');
    }

    /**
     * Display admin's home page
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('Admin panel'));
        $this->siteDesc = _t('Admin panel');
    }
}
