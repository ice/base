<?php

namespace App\Modules\Admin\Controllers;

use App\Extensions\Admin;

/**
 * Admin home controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class IndexController extends Admin
{

    /**
     * Display admin's home page
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('adminPanel'));
        $this->app->description = _t('adminPanel');
    }
}
