<?php
include_once '../models/Station.php';
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 27.10.16
 * Time: 14:36
 */
class StationController
{

    /**
     * @param string $data {"model":"station","action":"update_cart","data":{"id":"123", "balance": 20, "is_admin": 1}}
     * @return array
     */
    public static function actionUpdateCart($data)
    {
        $data = '{"model":"station","action":"card_found","data":{"id":"1"}}';
        $request = json_decode($data);
        $station = new \Model\Station($request->data->id);
        $response =  $station->Start($request->data);

        echo $response['message'];
    }

}