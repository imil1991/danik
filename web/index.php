<?php
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 27.10.16
 * Time: 13:04
 */
define('ROOT',dirname(__DIR__).'/');
error_reporting(E_ALL);
$config = include ROOT.'config/web.php';
include ROOT.'libs/Router.php';
include ROOT.'libs/Log.php';
$router = new Router($config);
$router->run();