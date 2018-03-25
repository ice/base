<?php

require_once __DIR__ . '/../root.php';
$loader = include_once __DIR__ . '/../autoload.php';

$loader
    ->addNamespace('Tests', __ROOT__ . '/tests')
    ->register();
