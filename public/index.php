<?php

/**
 * Set the PHP error reporting level.
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 */
error_reporting(E_ALL | E_STRICT);

defined('__ROOT__') or
    /**
     * Full path to the docroot
     */
    define('__ROOT__', dirname(__DIR__));

if (!function_exists('__')) {
    /**
     * Translation function
     * @see Ice\I18n translate()
     */
    function __($string, array $values = null, $lang = null)
    {
        return \Ice\I18n::fetch()->translate($string, $values, $lang);
    }

}

// Register App namespace
(new Ice\Loader())
    ->addNamespace('App', __ROOT__ . '/app')
    ->register();

// Include composer's autolader
include_once __ROOT__ . '/vendor/autoload.php';

// Initialize website, handle a MVC request and display the HTTP response body
$app = (new App\Application((new Ice\Di())->errors('App\Error')))->initialize();

// Handle a MVC request and display the HTTP response body
if (PHP_SAPI == 'cli') {
    return $app->handle("GET", isset($argv[1]) ? $argv[1] : null);
} else {
    echo $app->handle();
}
