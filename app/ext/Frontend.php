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

        $this->assets
            // Add styles to assets
            ->add('css/frontend.css', $this->config->assets->frontend)
            // Add scripts to assets
            ->add('js/frontend.js', $this->config->assets->frontend);
    }
}
