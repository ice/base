<?php

namespace App\Modules\Admin\Controllers;

use App\Extensions\Admin;

/**
 * Admin home controller.
 *
 * @category Controller
 * @package  App
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class IndexController extends Admin
{

    /**
     * Display home page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('adminPanel'));
        $this->app->description = _t('adminPanel');
    }
}
