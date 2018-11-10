<?php

$loader = include __DIR__ . '/../autoload.php';
$loader
    ->addNamespace('Tests', __ROOT__ . '/tests')
    ->register();
