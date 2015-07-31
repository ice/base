<?php

namespace App\Modules\Doc\Controllers;

use App\Extensions\Controller;

/**
 * Documentation home controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class IndexController extends Controller
{

    
    public function before()
    {
        parent::before();
        
        $this->assets->add('css/highlight/tomorrow.min.css', '8.3');
        $this->assets->add('js/plugins/highlight.min.js', '8.3');
        $this->assets->addJs([
            'content' => '$(document).ready(function() {$("pre code").each(function(i, e) {hljs.highlightBlock(e)});});'
        ]);
    }

    /**
     * Display doc's home page
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('Documentation'));
        $this->app->description = _t('Documentation');
    }
}
