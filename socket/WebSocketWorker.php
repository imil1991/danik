<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 23.10.16
 * Time: 13:34
 */

namespace demon;

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
     * @param $master
     */
    public function __construct($server, $master)
    {
        $this->server = $server;
        $this->master = $master;
        $this->pid = posix_getpid();
    }

    public function start()
    {
        while (true) {
            # подготавливаем массив всех сокетов, которые нужно обработать
            $read = $this->clients;
            $read[] = $this->server;
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
                    $address = explode(':', stream_socket_get_name($client, true));
                    if (isset($this->ips[$address[0]]) && $this->ips[$address[0]] > 5) { # блокируем более пяти соединий с одного ip
                        @fclose($client);
                    } else {
                        @$this->ips[$address[0]]++;

                        $this->clients[intval($client)] = $client;
                        $this->handshakes[intval($client)] = array(); # отмечаем, что нужно сделать рукопожатие
                    }
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
                    } else {
                        $data = fread($client, 1000);
                        if (!$data) {  # соединение было закрыто
                            unset($this->clients[intval($client)]);
                            unset($this->handshakes[intval($client)]);
                            $address = explode(':', stream_socket_get_name($client, true));
                            if (isset($this->ips[$address[0]]) && $this->ips[$address[0]] > 0) {
                                @$this->ips[$address[0]]--;
                            }
                            @fclose($client);
                            $this->onClose($client); # вызываем пользовательский сценарий
                            continue;
                        }

                        $this->onMessage($client, $data); # вызываем пользовательский сценарий
                    }
                }
            }

            if ($write) {
                foreach ($write as $client) {
                    if (!$this->handshakes[intval($client)]) { # если ещё не было получено рукопожатие от клиента
                        continue; # то отвечать ему рукопожатием ещё рано
                    }
                    $info = $this->handshake($client);
                    $this->onOpen($client, $info); # вызываем пользовательский сценарий
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
            echo $data.PHP_EOL;
            $decodedData = json_decode($data);
            if($decodedData->model == 'station'){
                if($decodedData->action == 'set_id'){
                    $key = $decodedData->data->id;
                    if($key == 'master'){
                        $this->master = $client;
                    }
                }
            }

            $this->handshakes[intval($client)] = $key;
        }

        return $key;
    }

    protected function encode($payload, $type = 'text', $masked = false)
    {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                #  first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                #  first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                #  first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                #  first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        #  set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            #  most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        #  convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            #  generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        #  append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
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