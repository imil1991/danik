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
use Model\Plug;
use Model\Station;

class WebSocketHandler extends WebSocketWorker{


    /** вызывается при получении сообщения от клиента
     * @param $client
     * @param $data
     */
    protected function sendToServer($client, $data)
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
            }break;
            case 'plug':
            {
                $obj = new Plug(intval($client));
            }break;
            default:
                $obj = new Station(intval($client));
                break;
        }
        $response = $obj->$action($data);

        if(!empty($response['message'])){
            @fwrite(current($this->master), $response['message']);
        }
    }

    protected function sendToClients($data)
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
