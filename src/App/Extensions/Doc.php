<?php

namespace App\Extensions;

/**
 * Doc controller.
 *
 * @category Extensions
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Doc extends Controller
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
            ->add('css/doc.css', $this->config->assets->doc)
            ->add('css/highlight/tomorrow.min.css', $this->config->assets->highlight)
            ->add('js/plugins/highlight.min.js', $this->config->assets->highlight)
            ->add('js/doc.js', $this->config->assets->doc);

        $this->app->layout->replace([
            'body' => 'pb-sm-3',
            'main' => 'container',
        ]);
    }
}
