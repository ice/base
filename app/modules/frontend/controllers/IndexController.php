<?php

namespace App\Modules\Frontend\Controllers;

use App\Extensions\Controller;

/**
 * Frontend home controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class IndexController extends Controller
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
