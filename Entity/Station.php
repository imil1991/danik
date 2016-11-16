<?php
namespace Entity;
use Model\StationRepository;

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
    private $stationId;
    /**
     * @var int
     */
    private $processCardId;
    /**
     * @var int
     */
    private $imei;
    /**
     * @var Plug
     */
    private $plugStatus;
    /**
     * @var Plug
     */
    private $plugStatusMessage;

    /**
     * @var Card
     */
    private $card;

    public function getStationId()
    {
        return $this->stationId;
    }

    /**
     * @param $stationId
     * @return $this
     */
    public function setStationId($stationId)
    {
        $this->stationId = $stationId;

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

    public function getProcessCardId()
    {
        return $this->processCardId;
    }

    /**
     * @param $processCardId
     * @return $this
     */
    public function setProcessCardId($processCardId)
    {
        $this->processCardId = $processCardId;

        return $this;
    }

    public function getPlugStatusMessage()
    {
        return $this->plugStatusMessage;
    }

    /**
     * @param $plugStatusMessage
     * @return $this
     */
    public function setPlugStatusMessage($plugStatusMessage)
    {
        $this->plugStatusMessage = $plugStatusMessage;

        return $this;
    }

    /**
     * @return Plug
     */
    public function getPlugStatus()
    {
        return $this->plugStatus;
    }

    /**
     * @param Plug $plugStatus
     */
    public function setPlugs(Plug $plugStatus)
    {
        $this->plugStatus = $plugStatus;
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

    /**
     * @return string
     */
    public function getMessageId()
    {
        return 'Станция №'.$this->getStationId().PHP_EOL;
    }

    public function toArray()
    {
        return [
            'stationId' => $this->getStationId(),
            'imei' => $this->getIemi(),
            'plugStatus' => $this->getPlugStatus()->getPlugs(),
            'plugStatusMessage' => $this->getPlugStatusMessage(),
            'processCartId' => $this->getProcessCardId()
        ];
    }

    public function save(){
        $this->getCard()->save();
        $rep = new StationRepository();
        $rep->update(['stationId'=>$this->getStationId()],$this->toArray());
    }
}