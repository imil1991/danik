<?php

/**
 * Created by PhpStorm.
 * User: bona
 * Date: 07.11.16
 * Time: 18:07
 */
class Log
{

    private $dir = ROOT.'logs/app/';

    private static $instance;

    public static function singleton()
    {
        if(self::$instance)
            return self::$instance;

        return self::$instance = new Log();
    }

    public function add($class, $message)
    {
        @file_put_contents($this->dir.$class.'.log',$message.PHP_EOL,'a+');
    }
}