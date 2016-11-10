<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 23.10.16
 * Time: 13:28
 * Class WebSocketServer https://habrahabr.ru/post/209864/
 */
namespace demon;
require 'WebSocketMaster.php';
require 'WebSocketHandler.php';
use demon\WebSocketMaster;
use demon\WebSocketHandler;

class WebSocketServer {

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function start()
    {
        $server = stream_socket_server("tcp://{$this->config['host']}:{$this->config['port']}", $errorNumber, $errorString);

        if (!$server) {
            die("error: stream_socket_server: $errorString ($errorNumber)\r\n");
        }

        $WebSocketHandler = new WebSocketHandler($server);
        $WebSocketHandler->start();
    }

}