<?php

namespace App\Extensions;

/**
 * Frontend controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class Frontend extends Controller
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
            // Add scripts to assets
            ->add('js/jquery.min.js', $versions->jquery)
            ->add('js/material.min.js', $versions->material)
            ->add('js/frontend.js', $versions->frontend);
    }
}
