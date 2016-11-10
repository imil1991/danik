<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 23.10.16
 * Time: 13:34
 */

namespace demon;
require_once 'models/Station.php';
abstract class WebSocketWorker
{

    protected $clients = array();
    protected $server;
    protected $master;
    protected $pid;
    protected $handshakes = array();
    protected $ips = array();

    /**
     * @param $server
     */
    public function __construct($server)
    {
        $this->server = $server;
        $this->pid = posix_getpid();
    }

    public function start()
    {
        while (true) {
            # подготавливаем массив всех сокетов, которые нужно обработать
            $read = $this->clients;
            $read[] = $this->server;
            if(!empty($this->master))
                $read[] = $this->master;

            $write = array();
            if ($this->handshakes) {
                foreach ($this->handshakes as $clientId => $clientInfo) {
                    if ($clientInfo) {
                        $write[] = $this->clients[$clientId];
                    }
                }
            }

            stream_select($read, $write, $except, null); # обновляем массив сокетов, которые можно обработать

            if (in_array($this->server, $read)) {  # на серверный сокет пришёл запрос от нового клиента
                # подключаемся к нему и делаем рукопожатие, согласно протоколу вебсокета
                if ($client = stream_socket_accept($this->server, -1)) {
                    $this->clients[intval($client)] = $client;
                    $this->handshakes[intval($client)] = array(); # отмечаем, что нужно сделать рукопожатие
                }

                # удаляем сервеный сокет из массива, чтобы не обработать его в этом цикле ещё раз
                unset($read[array_search($this->server, $read)]);
            }

            if (in_array($this->master, $read)) {  # пришли данные от мастера
                $data = fread($this->master, 1000);
                $this->onSend($data); # вызываем пользовательский сценарий
                # удаляем мастера из массива, чтобы не обработать его в этом цикле ещё раз
                unset($read[array_search($this->master, $read)]);
            }

            if ($read) { # пришли данные от подключенных клиентов
                foreach ($read as $client) {

                    if (isset($this->handshakes[intval($client)])) {
                        if ($this->handshakes[intval($client)]) { # если уже было получено рукопожатие от клиента
                            $data = fread($client, 1000);
                            $this->onMessage($client, $data); # вызываем пользовательский сценарий
                            unset($read[$client]);
                            continue; # то до отправки ответа от сервера читать здесь пока ничего не надо
                        }

                        if (!$this->handshake($client)) {
                            unset($this->clients[intval($client)]);
                            unset($this->handshakes[intval($client)]);
                            $address = explode(':', stream_socket_get_name($client, true));
                            if (isset($this->ips[$address[0]]) && $this->ips[$address[0]] > 0) {
                                @$this->ips[$address[0]]--;
                            }

                            @fclose($client);
                        }
                    }
                }
            }
        }
    }

    protected function handshake($client)
    {
        $key = $this->handshakes[intval($client)];

        if (!$key) {
            # считываем загаловки из соединения
            $data = fread($client, 10000);
            $decodedData = json_decode($data);
            if($decodedData->model == 'station'){
                if($decodedData->action == 'set_id'){
                    $key = $decodedData->data->id;
                    $log = new \Log();
                    if($key == 'master'){
                        $log->add(__CLASS__,'Подключен сервер');
                        $this->master = $client;
                    } else {
                        $log->add(__CLASS__,'Подключена станция №' . $key);
                    }
                }
            }

            $this->handshakes[intval($client)] = $key;
        }

        return $key;
    }

     protected function decode($data)
    {
        $decodedData = json_decode($data);
        if($decodedData->model == 'station'){
            if($decodedData->action == 'set_id'){
                $this->handshakes[$decodedData->data] = $decodedData->data;
            }
        }

        return $decodedData;
    }

    abstract protected function onOpen($client, $info);

    abstract protected function onClose($client);

    abstract protected function onMessage($client, $data);

    abstract protected function onSend($data);

    abstract protected function send($data);

}
