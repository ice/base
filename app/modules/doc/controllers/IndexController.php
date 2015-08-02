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
        
        $this->assets->add('css/highlight/tomorrow.min.css', $this->config->assets->highlight);
        $this->assets->add('js/plugins/highlight.min.js', $this->config->assets->highlight);
        $this->assets->addJs([
            'content' => '$(document).ready(function() {$("pre code").each(function(i, e) {hljs.highlightBlock(e)});});'
        ]);
    }

    /**
     * Display doc's home page
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('documentation'));
        $this->app->description = _t('documentation');
    }
}
