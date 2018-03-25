<?php

namespace App\Modules\Doc\Controllers;

use App\Extensions\Doc;

/**
 * Documentation home controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class IndexController extends Doc
{

    /**
     * Display doc's home page
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('documentation'));
        $this->app->description = _t('documentation');
    }
}
