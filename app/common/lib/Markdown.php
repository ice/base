<?php

namespace App\Libraries;

use Ice\Di;
use Ice\Mvc\View\ViewInterface;
use Ice\Mvc\View\Engine;
use Ice\Mvc\View\Engine\EngineInterface;
use ParsedownExtra;

/**
 * Markdown template engine
 *
 * @package     Ice/Base
 * @category    Extension
 * @uses        ParsedownExtra
 */
class Markdown extends Engine implements EngineInterface
{

    private $parser;

    /**
     * Engine constructor
     *
     * @param ViewInterface $view
     * @param Di $di
     */
    public function __construct(ViewInterface $view, Di $di = null)
    {
        $this->parser = new ParsedownExtra();

        parent::__construct($view, $di);
    }

    /**
     * Renders a view using the template engine
     *
     * @param string  $path
     * @param array   $data
     * @return string
     */
    public function render($path, array $data = [])
    {
        $content = $this->parser->text(file_get_contents($path));
        $this->_view->setContent($content);
        return $content;
    }
}
