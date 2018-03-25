<?php

// Register App namespace
$loader = new Ice\Loader();
$loader
    ->addNamespace('App', __DIR__ . '/App')
    ->register();

// Include composer's autolader
$composer = include_once __DIR__ . '/vendor/autoload.php';

return $loader;
