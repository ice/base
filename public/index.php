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

require_once __DIR__ . '/../autoload.php';

use App\Boot\Base;
use Ice\Di;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
if (file_exists(__DIR__.'/../.env')) {
    // The load method will never overwrite environment variables. Usually this is what you want.
    // When you load the .env file file you generally only want to load the variables that are not yet defined.
    // For example, when using Nginx you may set the APP_ENV variable using a fastcgi parameter. Another common use case
    // is when using a containerization technology such as docker. You would define environment variables in a
    // docker-compose.yml file (- or a secrets file in cloud mode. If you do want to overwrite existing variables than
    // you must use the overload() method instead of the load method.
    $dotenv->load(__DIR__.'/../.env');

    // You can also load several files
    //$dotenv->load(__DIR__.'/.env', __DIR__.'/.env.dev');
}

// Initialize website, handle a MVC request and display the HTTP response body
$app = (new Base(new Di))->initialize();

echo $app->handle($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);