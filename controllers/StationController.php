<?php
include ROOT.'models/Station.php';
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 27.10.16
 * Time: 14:36
 */
class StationController
{

    /**
     * @param $data {"model":"station","action":"update_cart","data":{"id":"123", "balance": 20, "is_admin": 1}}
     * @return array
     */
    public function actionUpdateCart($data)
    {
        $station = new Station;
        $series = $station->getSeriesById($data->id);
        $response = [
            'message' => $station->prepareMessage('EDIT'.$series.$data->balance.$data->admin.$data->plug)
        ];

        echo $response['message'];
    }

}