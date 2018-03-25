<?php

namespace App\Modules\Doc\Controllers;

use App\Extensions\Doc;

/**
 * Documentation home controller.
 *
 * @category Controller
 * @package  App
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class IndexController extends Doc
{

    /**
     * Display home page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('documentation'));
        $this->app->description = _t('documentation');
    }
}
