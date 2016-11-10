<?php

/**
 * Created by PhpStorm.
 * User: bona
 * Date: 07.11.16
 * Time: 18:07
 */
class Log
{

    private $dir = 'logs/app/';

    private static $instance;

    public static function singleton()
    {
        if(self::$instance)
            return self::$instance;

        return self::$instance = new Log();
    }

    public function add($class, $message)
    {
        $filename = self::normalizeString($class.'.log');
        $date = date('H:i:s Y-m-d');
        $message = PHP_EOL.$date . '  ' . $message . PHP_EOL;
        file_put_contents($this->dir.$filename,$message,FILE_APPEND);
    }

    public static function normalizeString ($str = '')
    {
        $str = str_replace('\\','-',$str);
        $str = strip_tags($str);
        $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
        $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
        $str = strtolower($str);
        $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
        $str = htmlentities($str, ENT_QUOTES, "utf-8");
        $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
        $str = str_replace(' ', '-', $str);
        $str = rawurlencode($str);
        $str = str_replace('%', '-', $str);
        return $str;
    }
}