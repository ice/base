<?php

namespace App\Modules\Frontend\Controllers;

use App\Extensions\Frontend;

/**
 * Frontend home controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class IndexController extends Frontend
{

    /**
     * Display home page
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('home'));
        $this->app->description = _t('home');
    }
}
