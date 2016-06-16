<?php

namespace App;

/**
 * App routes
 *
 * @package     Ice/Base
 * @category    Router
 */
class Routes
{

    public function universal()
    {
        return [
            // Routes for any module
            ['GET', '/module/{module:[a-z]+}/{controller:[a-z]+}/{action:[a-z]+}[/{id:\d+}[/{param}]]'],
            ['GET', '/module/{module:[a-z]+}/{controller:[a-z]+}/{action:[a-z]+}/{param:[a-z]+}'],
            ['GET', '/module/{module:[a-z]+}/{controller:[a-z]+}[/{id:\d+}]'],
            ['GET', '/module/{module:[a-z]+}[/]'],
            // Routes for admin module
            ['GET', '/{module:admin|doc}/{controller:[a-z]+}/{action:[a-z]+}[/{id:\d+}[/{param}]]'],
            ['GET', '/{module:admin|doc}/{controller:[a-z]+}/{action:[a-z]+}/{param:[a-z]+}'],
            ['GET', '/{module:admin|doc}/{controller:[a-z]+}[/{id:\d+}]'],
            ['GET', '/{module:admin|doc}[/]'],
            // Static routes
            [['GET', 'POST'], '/contact', ['controller' => 'static', 'action' => 'contact']],
            // Routes for default module
            ['*', '/{controller:[a-z]+}/{action:[a-z]+}[/{id:\d+}[/{param}]]'],
            [['GET', 'POST'], '/{controller:[a-z]+}/{action:[a-z]+}/{param:[a-z]+}'],
            [['GET', 'POST'], '/{controller:[a-z]+}[/{id:\d+}]'],
            [['GET', 'POST'], ''],
        ];
    }
}
