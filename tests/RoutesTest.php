<?php

namespace Tests;

use PHPUnit_Framework_TestCase as PHPUnit;
use Ice\Di;
use Ice\Mvc\Router;
use App\Routes;

class RoutesTest extends PHPUnit
{

    /**
     * Test route matching for universal routes and GET method
     *
     * @dataProvider GETrouteProvider
     */
    public function testUniversalGET($pattern, $expected)
    {
        $di = new Di();
        $router = new Router();
        $router->setDefaultModule('frontend');
        $router->setRoutes((new Routes())->universal());
        $return = $router->handle('GET', $pattern);

        $this->assertEquals('GET', $router->getMethod());

        if (is_array($return)) {
            $this->assertEquals($expected, [$router->getModule(), $router->getHandler(), $router->getAction(), $router->getParams()], $pattern);
        } else {
            $this->assertEquals($expected, null, "The route wasn't matched by any route");
        }
    }

    /**
     * Test route matching for universal routes and POST method
     *
     * @dataProvider POSTrouteProvider
     */
    public function testUniversalPOST($pattern, $expected)
    {
        $di = new Di();
        $router = new Router();
        $router->setDefaultModule('frontend');
        $router->setRoutes((new Routes())->universal());
        $return = $router->handle('POST', $pattern);

        $this->assertEquals('POST', $router->getMethod());

        if (is_array($return)) {
            $this->assertEquals($expected, [$router->getModule(), $router->getHandler(), $router->getAction(), $router->getParams()], $pattern);
        } else {
            $this->assertEquals($expected, null, "The route wasn't matched by any route");
        }
    }

    /**
     * Routes provider for GET method
     * [pattern, expected route: [module, handler, action, [params]]]
     *
     * @return array
     */
    public function GETrouteProvider()
    {
        return [
            ['', ['frontend', 'index', 'index', []]],
            ['/index', ['frontend', 'index', 'index', []]],
            ['/index/index', ['frontend', 'index', 'index', []]],
            ['/index/test', ['frontend', 'index', 'test', []]],
            
            ['/user', ['frontend', 'user', 'index', []]],
            ['/user/', ['frontend', 'user', 'index', []]],
            ['/user/3', ['frontend', 'user', 'index', ['id' => 3]]],
            ['/user/signup', ['frontend', 'user', 'signup', []]],
            ['/user/profile/1', ['frontend', 'user', 'profile', ['id' => 1]]],
            ['/user/profile/ice', ['frontend', 'user', 'profile', ['param' => 'ice']]],

            ['/post/details/7/friendly-title', ['frontend', 'post', 'details', ['id' => 7, 'param' => 'friendly-title']]],
            
            ['/contact', ['frontend', 'static', 'contact', []]],

            ['/admin', ['admin', 'index', 'index', []]],
            ['/admin/', ['admin', 'index', 'index', []]],
            ['/admin/index', ['admin', 'index', 'index', []]],
            ['/admin/index/index', ['admin', 'index', 'index', []]],
            ['/admin/index/test', ['admin', 'index', 'test', []]],
            
            ['/admin/user', ['admin', 'user', 'index', []]],
            ['/admin/user/', ['admin', 'user', 'index', []]],
            ['/admin/user/3', ['admin', 'user', 'index', ['id' => 3]]],
            ['/admin/user/add', ['admin', 'user', 'add', []]],
            ['/admin/user/details/2', ['admin', 'user', 'details', ['id' => 2]]],
            ['/admin/user/details/ice', ['admin', 'user', 'details', ['param' => 'ice']]],
            
            ['/doc', ['doc', 'index', 'index', []]],
            ['/doc/index', ['doc', 'index', 'index', []]],
            ['/doc/index/index', ['doc', 'index', 'index', []]],
            ['/doc/index/test', ['doc', 'index', 'test', []]],
            
            ['/doc/install', ['doc', 'install', 'index', []]],
            ['/doc/install/requirements', ['doc', 'install', 'requirements', []]],
            ['/doc/install/requirements/php', ['doc', 'install', 'requirements', ['param' => 'php']]],
            ['/doc/examples/2', ['doc', 'examples', 'index', ['id' => 2]]],

            ['/module/some', ['some', 'index', 'index', []]],
            ['/module/some/', ['some', 'index', 'index', []]],
            ['/module/some/index', ['some', 'index', 'index', []]],
            ['/module/some/index/index', ['some', 'index', 'index', []]],
            ['/module/some/index/test', ['some', 'index', 'test', []]],

            ['/module/some/user', ['some', 'user', 'index', []]],
            ['/module/some/user/', ['some', 'user', 'index', []]],
            ['/module/some/user/3', ['some', 'user', 'index', ['id' => 3]]],
            ['/module/some/user/add', ['some', 'user', 'add', []]],
            ['/module/some/user/details/2', ['some', 'user', 'details', ['id' => 2]]],
            ['/module/some/user/details/ice', ['some', 'user', 'details', ['param' => 'ice']]],
        ];
    }

    /**
     * Routes provider for POST method
     * [pattern, expected route: [module, handler, action, [params]]]
     *
     * @return array
     */
    public function POSTrouteProvider()
    {
        return [
            ['', ['frontend', 'index', 'index', []]],
            ['/index', ['frontend', 'index', 'index', []]],
            ['/index/index', ['frontend', 'index', 'index', []]],
            ['/index/test', ['frontend', 'index', 'test', []]],
            
            ['/user', ['frontend', 'user', 'index', []]],
            ['/user/', ['frontend', 'user', 'index', []]],
            ['/user/3', ['frontend', 'user', 'index', ['id' => 3]]],
            ['/user/signup', ['frontend', 'user', 'signup', []]],
            ['/user/profile/1', ['frontend', 'user', 'profile', ['id' => 1]]],
            ['/user/profile/ice', ['frontend', 'user', 'profile', ['param' => 'ice']]],
            
            ['/post/details/7/friendly-title', ['frontend', 'post', 'details', ['id' => 7, 'param' => 'friendly-title']]],

            ['/contact', ['frontend', 'static', 'contact', []]],
        ];
    }
}
