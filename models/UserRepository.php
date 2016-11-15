<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 10.11.16
 * Time: 21:56
 */
namespace Model;


class UserRepository
{

    private $db, $collection;

    /**
     * StationRepository constructor.
     */
    public function __construct()
    {

        $m  = new \MongoClient("mongodb://5.101.105.227:27017");
        $this->db  = $m->{'eline-dev'};
        $this->collection = $this->db->{'users'};
    }


    /**
     * @param $id
     * @return Station
     */
    public function findById($id)
    {
        return $this->collection->findOne(['_id' => $id]);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->collection->find();
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