<?php
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 27.10.16
 * Time: 13:04
 */
define('ROOT',dirname(__DIR__).'/');
$config = include ROOT.'config/web.php';
include ROOT.'libs/Router.php';
$router = new Router($config);
$router->run();