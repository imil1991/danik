<?php

/**
 * Created by PhpStorm.
 * User: bona
 * Date: 27.10.16
 * Time: 14:14
 */
class Router {

    private $routes;

    function __construct($config){
        $this->routes = $config['route'];
    }

    function getURI(){
        if(!empty($_SERVER['REQUEST_URI'])) {
            return trim($_SERVER['REQUEST_URI'], '/');
        }

        if(!empty($_SERVER['PATH_INFO'])) {
            return trim($_SERVER['PATH_INFO'], '/');
        }

        if(!empty($_SERVER['QUERY_STRING'])) {
            return trim($_SERVER['QUERY_STRING'], '/');
        }
    }

    function run(){
        $uri = $this->getURI();
        foreach($this->routes as $pattern => $route){
            if(preg_match("~$route~", $uri)){
                $internalRoute = $route;
                $segments = explode('/', $internalRoute);
                $controller = ucfirst(array_shift($segments)).'Controller';
                $action = 'action'.ucfirst(array_shift($segments));
                $controllerFile = ROOT.'controllers/'.$controller.'.php';
                $data = [];
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = @file_get_contents("php://input", "r");
                    $data = json_decode($data);
                }
                if(file_exists($controllerFile)){
                    include($controllerFile);
                }

                if(!is_callable(array($controller, $action))){
                    header("HTTP/1.0 404 Not Found");
                    return;
                }
                call_user_func_array(array($controller, $action), [$data]);
            }
        }

        header("HTTP/1.0 404 Not Found");
        return;
    }
}