#!/usr/bin/php
<?php

require_once __DIR__ . '/../autoload.php';

// Initialize the application, handle a MVC request and display the HTTP response body
(new App\Boot\Console((new Ice\Di())))
    ->initialize()
    ->handle($argv);
