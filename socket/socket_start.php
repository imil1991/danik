#!/usr/bin/env php
<?php

error_reporting(E_ALL);
$config = include 'config/web.php';
include 'libs/Log.php';
require_once('WebSocketServer.php');
use demon\WebSocketServer;
$WebSocketServer = new WebsocketServer($config['socket']);
$WebSocketServer->start();