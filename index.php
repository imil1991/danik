#!/usr/bin/env php
<?php
error_reporting(E_ERROR);
require_once('WebSocketServer.php');
use demon\WebSocketServer;
$config = array(
    'host' => '0.0.0.0',
    'port' => 8000,
    'workers' => 1,
);
$WebSocketServer = new WebsocketServer($config);
$WebSocketServer->start();