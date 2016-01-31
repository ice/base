<?php

namespace App\Libraries;

use Ice\Di;
use Ice\Mvc\View\Engine;
use Ice\Mvc\View\Engine\EngineInterface;
use Ice\Mvc\View\ViewInterface;

/**
 * Twig template engine
 *
 * @package     Ice/Base
 * @category    Extension
 * @uses        Twig
 */
class Twig extends Engine implements EngineInterface
{

    protected $twig;

    /**
     * Engine constructor. Set the view and di object.
     *
     * @param ViewInterface $view
     * @param Di $di
     * @param array $options
     * @param array $functions
     */
    public function __construct(ViewInterface $view, Di $di = null, $options = [], $functions = [])
    {
        $loader = new \Twig_Loader_Filesystem('/');
        $this->twig = new \Twig_Environment($loader, $options);
        $this->twig->addExtension(new \Twig_Extension_Core());
        $this->registryFunctions($view, $di, $functions);

        parent::__construct($view, $di);
    }

    /**
     * Registers common function in Twig.
     *
     * @param ViewInterface $view
     * @param Di $di
     * @param array $userFunctions
     */
    protected function registryFunctions($view, $di, $userFunctions = [])
    {
        $options = [
            'is_safe' => ['html']
        ];

        $functions = [
            new \Twig_SimpleFunction('content', function () use ($view) {
                return $view->getContent();
            }, $options),
            new \Twig_SimpleFunction('partial', function ($partialPath) use ($view) {
                return $view->partial($partialPath);
            }, $options),
            new \Twig_SimpleFunction('load', function ($partialPath) use ($view) {
                return $view->load($partialPath);
            }, $options),
            new \Twig_SimpleFunction('dump', function ($parameters) use ($di) {
                return $di->get('dump')->vars($parameters);
            }, $options),
            new \Twig_SimpleFunction('version', function () {
                return \Ice\Version::get();
            }, $options),
        ];

        // Add all Tag's functions
        $tag = new \ReflectionClass("Ice\\Tag");
        $methods = $tag->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            switch ($method->name) {
                case "__construct":
                    continue;
                default:
                    // Add linkTo, etc
                    $name = $method->name;
                    $functions[] = new \Twig_SimpleFunction($name, function ($parameters) use ($di, $name) {
                        return $di->tag->{$name}($parameters);
                    }, $options);

                    // Add link_to, etc
                    $uncamelized = $di->filter->sanitize($name, 'uncamelize');
                    $functions[] = new \Twig_SimpleFunction($uncamelized, function ($parameters) use ($di, $name) {
                        return $di->tag->{$name}($parameters);
                    }, $options);
            }
        }

        if (!empty($userFunctions)) {
            $functions = array_merge($functions, $userFunctions);
        }

        foreach ($functions as $function) {
            $this->twig->addFunction($function);
        }
    }

    /**
     * Render some view.
     *
     * @param string $path
     * @param array $params
     */
    public function render($path, array $params = [])
    {
        $view = $this->view;

        if (!isset($params['content'])) {
            $params['content'] = $view->getContent();
        }

        if (!isset($params['view'])) {
            $params['view'] = $view;
        }

        return $this->twig->render($path, $params);
    }

    /**
     * Returns Twig environment object.
     *
     * @return object Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }
}
