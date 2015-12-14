<?php

namespace App\Extensions;

/**
 * Doc controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class Doc extends Controller
{

    /**
     * Before execute action
     *
     * @return void
     */
    public function before()
    {
        parent::before();

        $this->assets
            // Add styles to assets
            ->add('css/frontend.css', $this->config->assets->frontend)
            ->add('css/highlight/tomorrow.min.css', $this->config->assets->highlight)
            // Add scripts to assets
            ->add('js/frontend.js', $this->config->assets->frontend)
            ->add('js/plugins/highlight.min.js', $this->config->assets->highlight)
            ->addJs([
                'content' => '$(document).ready(function() {
                    $("pre code").each(function(i, e) {
                        hljs.highlightBlock(e);
                    });
                });'
            ]);
    }
}
