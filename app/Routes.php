<?php

namespace App;

/**
 * App routes
 *
 * @package     Ice/Base
 * @category    Router
 * @version     1.0
 */
class Routes
{

    public function universal()
    {
        return [
            // Routes for any module
            ['GET', '/module/{module:[a-z]+}/{controller:[a-z]+}/{action:[a-z]+}/{id:\d+}'],
            ['GET', '/module/{module:[a-z]+}/{controller:[a-z]+}/{action:[a-z]+}/{param}'],
            ['GET', '/module/{module:[a-z]+}/{controller:[a-z]+}/{action:[a-z]+[/]?}'],
            ['GET', '/module/{module:[a-z]+}/{controller:[a-z]+}/{id:\d+}'],
            ['GET', '/module/{module:[a-z]+}/{controller:[a-z]+[/]?}'],
            ['GET', '/module/{module:[a-z]+[/]?}'],
            // Routes for admin module
            ['GET', '/{module:admin}/{controller:[a-z]+}/{action:[a-z]+}/{id:\d+}'],
            ['GET', '/{module:admin}/{controller:[a-z]+}/{action:[a-z]+}/{param}'],
            ['GET', '/{module:admin}/{controller:[a-z]+}/{action:[a-z]+[/]?}'],
            ['GET', '/{module:admin}/{controller:[a-z]+}/{id:\d+}'],
            ['GET', '/{module:admin}/{controller:[a-z]+[/]?}'],
            ['GET', '/{module:admin+[/]?}'],
            // Routes for doc module
            ['GET', '/{module:doc}/{controller:[a-z]+}/{action:[a-z]+}/{id:\d+}'],
            ['GET', '/{module:doc}/{controller:[a-z]+}/{action:[a-z]+}/{param}'],
            ['GET', '/{module:doc}/{controller:[a-z]+}/{action:[a-z]+[/]?}'],
            ['GET', '/{module:doc}/{controller:[a-z]+}/{id:\d+}'],
            ['GET', '/{module:doc}/{controller:[a-z]+[/]?}'],
            ['GET', '/{module:doc+[/]?}'],
            // Static routes
            ['GET', '/contact', ['controller' => 'static', 'action' => 'contact']],
            ['POST', '/contact', ['controller' => 'static', 'action' => 'contact']],
            // Routes for default module
            ['GET', '/{controller:[a-z]+}/{action:[a-z]+}/{id:\d+}/{param}'],
            ['GET', '/{controller:[a-z]+}/{action:[a-z]+}/{id:\d+}'],
            ['GET', '/{controller:[a-z]+}/{action:[a-z]+}/{param}'],
            ['GET', '/{controller:[a-z]+}/{action:[a-z]+[/]?}'],
            ['GET', '/{controller:[a-z]+}/{id:\d+}'],
            ['GET', '/{controller:[a-z]+[/]?}'],
            ['GET', ''],
            ['POST', '/{controller:[a-z]+}/{action:[a-z]+}/{id:\d+}/{param}'],
            ['POST', '/{controller:[a-z]+}/{action:[a-z]+}/{id:\d+}'],
            ['POST', '/{controller:[a-z]+}/{action:[a-z]+}/{param}'],
            ['POST', '/{controller:[a-z]+}/{action:[a-z]+[/]?}'],
            ['POST', '/{controller:[a-z]+}/{id:\d+}'],
            ['POST', '/{controller:[a-z]+[/]?}'],
            ['POST', ''],
        ];
    }
}
