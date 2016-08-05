<?php

require_once '../Router.php';
require_once '../Application.php';

$router = new \Router;
$router->resource('posts');

Application::getInstance($router)->run();