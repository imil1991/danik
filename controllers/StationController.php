<?php
include '../models/StationRepository.php';
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
    public function actionUpdateCart()
    {
        $station = new \Model\StationRepository();
        $data = $station->findById(1);
        print_r($data->getCard()->getId());exit;
        $response = [
            'message' => ''
        ];

        echo $response['message'];
    }

}