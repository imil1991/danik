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
        # открываем серверный сокет
        $server = stream_socket_server("tcp://{$this->config['host']}:{$this->config['port']}", $errorNumber, $errorString);

        if (!$server) {
            die("error: stream_socket_server: $errorString ($errorNumber)\r\n");
        }

        list($pid, $master, $workers) = $this->spawnWorkers(); # создаём дочерние процессы
        if ($pid) { # мастер
            fclose($server); # мастер не будет обрабатывать входящие соединения на основном сокете
            $WebSocketMaster = new WebSocketMaster($workers); # мастер будет пересылать сообщения между воркерами
            $WebSocketMaster->start();
        } else { # воркер
            $WebSocketHandler = new WebSocketHandler($server, $master);
            $WebSocketHandler->start();
        }
    }

    protected function spawnWorkers()
    {
        $master = null;
        $workers = array();
        $i = 0;
        while ($i < $this->config['workers']) {
            $i++;
            # создаём парные сокеты, через них будут связываться мастер и воркер
            $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            $pid = pcntl_fork(); # создаём форк

            if ($pid == -1) {
                die("error: pcntl_fork\r\n");
            } elseif ($pid) {  # мастер
                fclose($pair[0]);
                $workers[$pid] = $pair[1]; # один из пары будет в мастере

            } else {  # воркер
                fclose($pair[1]);
                $master = $pair[0]; # второй в воркере
                break;
            }
        }

        return array($pid, $master, $workers);
    }
}