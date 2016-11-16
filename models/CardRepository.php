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
        
        $m  = new \MongoClient("mongodb://5.101.105.227:27017");
        $this->db  = $m->{'eline-dev'};
        $this->collection = $this->db->{'cards'};
    }


    /**
     * @param $id
     * @return Station
     */
    public function findById($id)
    {
        return $this->collection->findOne(['id' => $id]);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $cards = $this->collection->find();
        return $cards;
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