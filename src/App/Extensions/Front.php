<?php

namespace App\Extensions;

/**
 * Front controller.
 *
 * @category Extensions
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Front extends Controller
{

    /**
     * Before execute action.
     *
     * @return void
     */
    public function before()
    {
        parent::before();

        $this->assets
            ->add('css/front.css', $this->config->assets->front)
            ->add('js/front.js', $this->config->assets->front);

        $this->app->layout->replace([
            'main' => 'container',
        ]);
    }
}
