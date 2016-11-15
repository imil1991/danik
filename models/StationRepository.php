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
        $station = $this->collection->findOne(['stationId' => $id]);
        return $station;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $stations = $this->collection->find();
        return $stations;
    }

    /**
     * @param array $filter
     * @param array $data
     * @return $this
     */
    public function update(array $filter, array $data)
    {
        try {
            $this->collection->update($filter, ['$set' => $data]);
        } catch (\MongoCursorException $e){
            echo $e->getMessage();
        }
        return $this;
    }



}