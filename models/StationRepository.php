<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 10.11.16
 * Time: 21:56
 */
namespace Model;


use Entity\Card;
use Entity\Plug;
use Entity\Station;
include '../Entity/Station.php';
include '../Entity/Plug.php';
include '../Entity/Card.php';
class StationRepository
{

    private $db, $collection;

    /**
     * StationRepository constructor.
     */
    public function __construct()
    {
        $m  = new \MongoClient("mongodb://5.101.105.227:27017");
        $this->db  = $m->{'eline-dev'};
        $this->collection = $this->db->{'stations'};
    }


    /**
     * @param $id
     * @return Station
     */
    public function findById($id)
    {
        $result = new Station;
        $station = $this->collection->findOne(['stationId' => $id]);
        $result->setCard((new Card())->setId($station['_id']));
        $result->setId($station['_id']);
        $result->setImei($station['imei']);
        $result->setPlugs((new Plug())->setPlugs($station['plugStatus']));
        return $result;
    }



}