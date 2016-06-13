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

        $versions = $this->config->assets;
        $this->assets
            // Add styles to assets
            ->add('css/material.min.css', $versions->material)
            ->add('css/fonts.css', $versions->fonts)
            ->add('css/simple-line-icons.css', $versions->simplelineicons)
            ->add('css/material-icons.css', $versions->icons)
            ->add('css/frontend.css', $versions->frontend)
            ->add('css/highlight/tomorrow.min.css', $versions->highlight)
            // Add scripts to assets
            ->add('js/jquery.min.js', $versions->jquery)
            ->add('js/material.min.js', $versions->material)
            ->add('js/frontend.js', $versions->frontend)
            ->add('js/plugins/highlight.min.js', $versions->highlight)
            ->addJs([
                'content' => '$(document).ready(function() {
                    $("pre").each(function(i, e) {
                        hljs.registerLanguage("markup", function() {return hljs.getLanguage("html")} );
                        hljs.highlightBlock(e);
                    });
                });'
            ]);

            $this->app->layout->replace([
                'left' => 'mdl-cell--hide-desktop',
                'content' => 'mdl-cell--12-col',
                'ribbon' => 'mdl-color--light-green'
            ]);
    }
}
