<?php

namespace App\Boot;

/**
 * App routes
 *
 * @category Boot
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Routes
{

    /**
     * Universal routes.
     *
     * @return array
     */
    public function fastroute()
    {
        return [
            // Routes for modules
            ['*', '/{module:admin}[/{controller:[a-z-]+}[/{action:[a-z-]+}[/{id}[/{param}]]]]'],
            ['*', '/{module:doc}[/{controller:[a-z-]+}[/{action:[a-z-]+}[/{id}[/{param}]]]]'],

            // Routes for frontend
            ['*', '/{controller:[a-z]+}[/{action:[a-z-]+}[/{id}[/{param}]]]'],

            // Static routes
            [['GET', 'POST'], '/contact', ['controller' => 'index', 'action' => 'contact']],
            ['GET', '/{action:aboutus|policy|terms|lang}[/{id}]', ['controller' => 'index']],
            [['GET', 'POST'], ''],
        ];
    }

    /**
     * Universal routes.
     *
     * @return array
     */
    public function universal()
    {
        return [
            // Routes for modules
            ['*', '/{module}[/{controller}[/{action}[/{id}[/{param}]]]]', [
                'module' => 'admin|doc', 'controller' => '[a-z]+', 'action' => '[a-z]+']
            ],

            // Static routes
            ['GET', '/{action}', ['action' => 'aboutus|policy|terms']],
            ['GET', '/lang/{id}', null, ['action' => 'lang']],
            [['GET', 'POST'], '/{action}', ['action' => 'contact']],
            [['GET', 'POST'], '[/]'],

            // Routes for frontend
            ['*', '/{controller}[/{action}[/{id}[/{param}]]]', ['controller' => '[a-z]+', 'action' => '[a-z]+']],
        ];
    }
}
