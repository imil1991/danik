<?php

/**
 * Created by PhpStorm.
 * User: bona
 * Date: 27.10.16
 * Time: 14:51
 */
class Station {

    public function setId($data)
    {
        print_r($data);
    }

    static $array = [
      1 => [
          'id' => '0x030x220xBB0xBB',
          'balance' => '',
          'admin' => '',
      ],
      2 => [
          'id' => '0x630x660xBE0xBB',
          'balance' => '',
          'admin' => '',
      ],
      3 => [
          'id' => '0x840x520x1A0x2B',
          'balance' => '',
          'admin' => '1',
      ],
      4 => [
          'id' => '0x630x630xA90xBB',
          'balance' => '',
          'admin' => '1',
      ],
    ];

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

    public function catd_found($id)
    {
        $parce = explode('',$id);
        $ancii = '';
        foreach ($parce as $one){
            $ancii .= ord($one);
        }
        echo $ancii.PHP_EOL;

        return $ancii;

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
     *
     * @param $data
     * @return array
     */
    public function updateCart($data)
    {
        $series = $this->getSeriesById($data->id);
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
        return array_filter(self::$array);
    }

}