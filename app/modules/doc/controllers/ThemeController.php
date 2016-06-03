<?php

namespace App\Modules\Doc\Controllers;

use App\Extensions\Doc;

/**
 * Documentation theme controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class ThemeController extends Doc
{

    /**
     * Display material theme
     */
    public function materialAction()
    {
        $this->tag->setTitle(_t('material'));
        $this->app->description = _t('material');

        $this->app->layout->replace([
            'left' => 'mdl-cell--hide-desktop',
            'content' => 'mdl-cell--12-col'
        ]);
    }
}
