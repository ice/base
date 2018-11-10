<?php

namespace Tests;

use App\Boot\Routes;
use Ice\Di;
use Ice\Mvc\Router;
use Ice\Mvc\Fastrouter;
use PHPUnit_Framework_TestCase as PHPUnit;

/**
 * Toure tests.
 *
 * @category Task
 * @package  App
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class RoutesTest extends PHPUnit
{

    private static $di;

    /**
     * Run public/index.php and fetch Di.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        $di = new Di();
        $di->set('router', function () {
            $router = new Router();
            $router->setDefaultModule('frontend');
            $router->setRoutes((new Routes())->universal());

            return $router;
        });

        $di->set('fastrouter', function () {
            $router = new Fastrouter();
            $router->setDefaultModule('frontend');
            $router->setRoutes((new Routes())->fastroute());

            return $router;
        });

        self::$di = $di;
    }

    /**
     * Get service from Di.
     *
     * @param string $service Name
     *
     * @return object Service
     */
    public function __get($service)
    {
        return self::$di->{$service};
    }

    /**
     * Test route matching for universal routes and GET method.
     *
     * @param string $pattern  Uri
     * @param array  $expected Output
     *
     * @dataProvider GETrouteProvider
     *
     * @return void
     */
    public function testUniversalGET($pattern, $expected)
    {
        if (!$expected) {
            $this->expectExceptionMessage("Unable to find a route to match the URI: " . $pattern);
        }

        $return = $this->router->handle('GET', $pattern);
        $this->assertEquals('GET', $this->router->getMethod());

        $this->assertEquals(
            $expected,
            [
                $this->router->getModule(),
                $this->router->getHandler(),
                $this->router->getAction(),
                $this->router->getParams()
            ],
            $pattern
        );
    }

    /**
     * Test route matching for fastroute routes and GET method.
     *
     * @param string $pattern  Uri
     * @param array  $expected Output
     *
     * @dataProvider GETrouteProvider
     *
     * @return void
     */
    public function testFastrouteGET($pattern, $expected)
    {
        if (!$expected) {
            $this->expectExceptionMessage("The requested route could not be found");
        }

        $returnFast = $this->fastrouter->handle('GET', $pattern);
        $this->assertEquals('GET', $this->fastrouter->getMethod());

        $this->assertEquals(
            $expected,
            [
                $this->fastrouter->getModule(),
                $this->fastrouter->getHandler(),
                $this->fastrouter->getAction(),
                $this->fastrouter->getParams()
            ],
            $pattern
        );
    }

    /**
     * Test route matching for universal routes and POST method.
     *
     * @param string $pattern  Uri
     * @param array  $expected Output
     *
     * @dataProvider POSTrouteProvider
     *
     * @return void
     */
    public function testUniversalPOST($pattern, $expected)
    {
        $return = $this->router->handle('POST', $pattern);
        $this->assertEquals('POST', $this->router->getMethod());

        if (is_array($return)) {
            $this->assertEquals(
                $expected,
                [
                    $this->router->getModule(),
                    $this->router->getHandler(),
                    $this->router->getAction(),
                    $this->router->getParams()
                ],
                $pattern
            );
        } else {
            $this->assertEquals($expected, null, "The route wasn't matched by any route");
        }
    }

    /**
     * Test route matching for fastroute routes and POST method.
     *
     * @param string $pattern  Uri
     * @param array  $expected Output
     *
     * @dataProvider POSTrouteProvider
     *
     * @return void
     */
    public function testFastroutePOST($pattern, $expected)
    {
        $returnFast = $this->fastrouter->handle('POST', $pattern);
        $this->assertEquals('POST', $this->fastrouter->getMethod());

        if (is_array($returnFast)) {
            $this->assertEquals(
                $expected,
                [
                    $this->fastrouter->getModule(),
                    $this->fastrouter->getHandler(),
                    $this->fastrouter->getAction(),
                    $this->fastrouter->getParams()
                ],
                $pattern
            );
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
            ['/user/signup', ['frontend', 'user', 'signup', []]],
            ['/user/profile/1', ['frontend', 'user', 'profile', ['id' => 1]]],
            ['/user/profile/ice', ['frontend', 'user', 'profile', ['id' => 'ice']]],

            ['/post/details/7/friendly-title',
                ['frontend', 'post', 'details', ['id' => 7, 'param' => 'friendly-title']]],

            ['/contact', ['frontend', 'index', 'contact', []]],
            ['/lang/en-gb', ['frontend', 'index', 'lang', ['id' => 'en-gb']]],

            ['/admin', ['admin', 'index', 'index', []]],
            ['/admin/index', ['admin', 'index', 'index', []]],
            ['/admin/index/index', ['admin', 'index', 'index', []]],
            ['/admin/index/test', ['admin', 'index', 'test', []]],

            ['/admin/user', ['admin', 'user', 'index', []]],
            ['/admin/user/add', ['admin', 'user', 'add', []]],
            ['/admin/user/details/2', ['admin', 'user', 'details', ['id' => 2]]],
            ['/admin/user/details/ice', ['admin', 'user', 'details', ['id' => 'ice']]],

            ['/doc', ['doc', 'index', 'index', []]],
            ['/doc/index', ['doc', 'index', 'index', []]],
            ['/doc/index/index', ['doc', 'index', 'index', []]],
            ['/doc/index/test', ['doc', 'index', 'test', []]],

            ['/doc/install', ['doc', 'install', 'index', []]],
            ['/doc/install/requirements', ['doc', 'install', 'requirements', []]],
            ['/doc/install/requirements/php', ['doc', 'install', 'requirements', ['id' => 'php']]],
            ['/a/b/c/1/2/3', null]
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
            ['/user/signup', ['frontend', 'user', 'signup', []]],
            ['/user/profile/1', ['frontend', 'user', 'profile', ['id' => 1]]],
            ['/user/profile/ice', ['frontend', 'user', 'profile', ['id' => 'ice']]],

            ['/post/details/7/friendly-title',
                ['frontend', 'post', 'details', ['id' => 7, 'param' => 'friendly-title']]],

            ['/contact', ['frontend', 'index', 'contact', []]],
        ];
    }
}
