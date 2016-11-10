<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 23.10.16
 * Time: 13:36
 */

namespace demon;
require_once 'WebSocketWorker.php';
use demon\WebSocketWorker;
use Model\Station;

class WebSocketHandler extends WebSocketWorker{

    /** вызывается при соединении с новым клиентом
     * @param $client
     * @param $info
     */
    protected function onOpen($client, $info)
    {
    }

    /** вызывается при закрытии соединения клиентом
     * @param $client
     */
    protected function onClose($client)
    {

    }

    /** вызывается при получении сообщения от клиента
     * @param $client
     * @param $data
     */
    protected function onMessage($client, $data)
    {
        $decodedData = json_decode($data);
        $action = preg_replace_callback("/(?:^|_)([a-z])/", function($matches) {
            return strtoupper($matches[1]);
        }, $decodedData->action);
        $data = $decodedData->data;

        $response = [];
        switch ($decodedData->model){
            case 'station':
            {
                $obj = new Station(intval($client));
                $response = $obj->$action($data);
            }break;
        }

        if(!empty($response['message'])){
            @fwrite($client, $response['message']);
        }
    }

    /** вызывается при получении сообщения от мастера
     * @param $data
     */
    protected function onSend($data)
    {
        $this->sendHelper($data);
    }

    /** отправляем сообщение на мастер, чтобы он разослал его на все воркеры
     * @param $message
     */
    protected function send($message)
    {
        @fwrite($this->master, $message);
    }

    private function sendHelper($data)
    {
        $decodedData = json_decode($data);
        $action = preg_replace_callback("/(?:^|_)([a-z])/", function($matches) {
            return strtoupper($matches[1]);
        }, $decodedData->action);
        $data = $decodedData->data;

        $response = [];
        switch ($decodedData->model){
            case 'station':
                {
                    $obj = new Station($data->station);
                    $response = $obj->$action($data);
                }break;
        }

        if(!empty($response['client_id'])){
            $client = $this->clients[array_search($response['client_id'],$this->handshakes)];
            @fwrite($client, $response['message']);
        }
        else {
            $write = $this->clients;
            if (stream_select($read, $write, $except, 0)) {
                foreach ($write as $client) {
                    @fwrite($client, $response['message']);
                }
            }
        }
    }
}
