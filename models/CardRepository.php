<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 10.11.16
 * Time: 21:56
 */
namespace Model;


class CardRepository
{

    private $db, $collection;

    /**
     * StationRepository constructor.
     */
    public function __construct()
    {
        $this->db  = new \MongoDB\Driver\Manager("mongodb://5.101.105.227:27017");
    }


    public function findById($id)
    {
        $query = new MongoDB\Driver\Query(['id' => $id]);
        $result = $this->db->executeQuery("eline-dev.cards", $query)->toArray();
        return $result;
    }



}