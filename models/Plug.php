<?php
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 11.11.16
 * Time: 19:03
 */

namespace Model;


class Plug
{

    /**
     * {"model":"plug","action":"unblock","data":{"id":1}}
     * @param $data
     * @return array
     */
    public function unblock($data)
    {
        return ['message' => '{"status":"ok", "message":"plug #'.$data->id.' is unblocked"}'];
    }

}