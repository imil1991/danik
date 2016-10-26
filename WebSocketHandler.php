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
        $data = $this->decode($data);

        if (!$data['payload']) {
            return;
        }

        if (!mb_check_encoding($data['payload'], 'utf-8')) {
            return;
        }

        # шлем всем сообщение, о том, что пишет один из клиентов
        $message = 'пользователь #' . intval($client) . ' (' . $this->pid . '): ' . strip_tags($data['payload']);
        $this->send($message);
        $this->sendHelper($message);
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
                    $obj = new Station();
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

class Station {

    public function setId($data)
    {
        print_r($data);
    }

    /** Меняет статус розетки пример:
     *  {"model":"station","action":"station_control","data":{"station":"000001", "plug": 1, "status": true }}
     * @param $data
     * @return array
     */
    public function stationControl($data)
    {
        $state = $data->station ? 'UNBLOCK' : 'BLOCK';
        $response = [
            'client_id' => $data->station,
            'message' => $this->prepareMessage($state.'::'.$data->plug)
        ];

        return $response;
    }

    /** Пакет на остановку заряда:
     *  {"model":"station","action":"station_stop_plug","data":{"station":"000002", "plug": 2}}
     * @param $data
     * @return array
     */
    public function stationStopPlug($data)
    {
        $response = [
            'client_id' => $data->station,
            'message' => $this->prepareMessage('STOP::'.$data->plug)
        ];

        return $response;
    }

    /** Обновление карты - отправляется всем
     *  {"model":"station","action":"update_cart","data":{"id":"123", "balance": 20, "is_admin": 1}}
     * @param $data
     * @return array
     */
    public function updateCart($data)
    {
        $series = $this->getSeriesById($data['id']);
        $response = [
            'message' => $this->prepareMessage('EDIT'.$series.$data->balance.$data->admin.$data->plug)
        ];

        return $response;
    }

    public function prepareMessage($message)
    {
        return sprintf('%0-10s',$message);
    }

    public function getSeriesById($id)
    {
        return chr(0xFA) . chr(0xAA) . chr(0x48) . chr(0x3A);
    }
}