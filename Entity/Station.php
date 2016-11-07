<?php
namespace Entity;
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 07.11.16
 * Time: 18:31
 */
class Station
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $imei;
    /**
     * @var Plug
     */
    private $plugs;

    /**
     * @var Card
     */
    private $card;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getIemi()
    {
        return $this->imei;
    }

    /**
     * @param $imei
     * @return $this
     */
    public function setImei($imei)
    {
        $this->imei = $imei;

        return $this;
    }

    /**
     * @return Plug
     */
    public function getPlugs()
    {
        return $this->plugs;
    }

    /**
     * @param Plug $plug
     */
    public function setPlugs(Plug $plug)
    {
        $this->plugs = $plug;
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param Card $card
     * @return $this
     */
    public function setCard(Card $card)
    {
        $this->card = $card;

        return $this;
    }
}