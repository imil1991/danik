<?php
namespace Model;
use Entity\Plug;
include_once '../models/CardRepository.php';
include_once '../models/UserRepository.php';
include_once '../models/StationRepository.php';
include_once '../models/Card.php';
include_once '../Entity/User.php';
include_once '../Entity/Card.php';
include_once '../Entity/Station.php';
include_once '../Entity/Plug.php';
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 27.10.16
 * Time: 14:51
 */
class Station {

    const SEPARATOR = ':';

    /**
     * @var \Entity\Station
     */
    private $station;

    public function __construct($id)
    {
        $this->setStation($id);
    }

    /**
     * @param $id
     * @return $this
     */
    public function setStation($id)
    {
        $station = new \Entity\Station();
        $stationData = new \Model\StationRepository();
        $stationData = $stationData->findById((int) $id);
        $station->setStationId($stationData['stationId']);
        $station->setImei($stationData['imei']);
        $station->setPlugStatusMessage($stationData['plugStatusMessage']);
        $station->setPlugs(
            (new Plug())
                ->setPlugs($stationData['plugStatus'])
        );
        $station->setCard(
            (new Card())
                ->setCard($stationData['processCardId'])
                ->getCard()
        );

        $this->station = $station;
        return $this;
    }

    /**
     * @return \Entity\Station
     */
    public function getStation()
    {
        return $this->station;
    }

    /** Меняет статус розетки пример:
     *  {"model":"station","action":"station_control","data":{"station":"1", "plug": 1, "status": true }}
     * @param $data
     * @return array
     */
    public static function stationControl($data)
    {
        $state = $data->station ? 'UNBLOCK' : 'BLOCK';
        $response = [
            'client_id' => $data->station,
            'message' => self::prepareMessage($state.self::SEPARATOR.$data->plug)
        ];

        return $response;
    }


    /** При обнаружении карты клиента
     * {"model":"station","action":"card_found","data":{"id":"0000001"}}
     * @param $data
     * @return mixed
     */
    public function CardFound($data)
    {
        $card = new Card();
        $card->setCard($data->id);
        $this->station->setProcessCardId((int) $data->id)->save();
        $message = 'CARD'
            .self::SEPARATOR.$card->getCard()->getId()
            .self::SEPARATOR.$card->getCard()->getUser()->getBalance()
            .self::SEPARATOR.$card->getCard()->getUser()->getIsAdmin();
        $response['message'] = self::prepareMessage($message);

        return $response;

    }

    /** При начале потребления тока по розеткам
     * @param $data
     * @return array
     */
    public function Start($data)
    {
        $this->station->getCard()->getUser()->setBalance(
            $this->station->getCard()->getUser()->getBalance() - 1
        );
        $this->station->getPlugStatus()->setPlugStatus($data->id,Plug::STATUS_BUSY);
        $response = [
            'message' => self::prepareMessage(
                'EDIT'
                .self::SEPARATOR.$this->station->getCard()->getId()
                .self::SEPARATOR.$this->station->getCard()->getUser()->getBalance()
                .self::SEPARATOR.$this->station->getCard()->getUser()->getIsAdmin()
            ),
        'client' => 'all'
        ];

        $this->station->save();

        return $response;
    }

    /** При окончании потребления тока по розеткам
     * @param $data
     */
    public function Stop($data)
    {
        $this->station
            ->setProcessCardId(0)
            ->getPlugStatus()->setPlugStatus($data->id,Plug::STATUS_OPEN);
        $this->station->save();
        $this->log('Окончание потребления тока'.PHP_EOL
            .'Потреблённая мощность - ' . $data->power . ' кВт*час');
     }

    public function OverCurrent($data)
    {
        $this->log('Превышение максимально допустимого тока по розетке #' . $data->id);
    }


    public function OpenCase($data)
    {
        $this->log('Обнаружено вскрытие станции #' . $data->id);
    }

    public function Waiting($data)
    {
        $this->log('Переход станции в режим ожидания потребления тока (9 вольт на пилоте) по розетке #'. $data->id);
    }

    public function Fault($data)
    {
        $this->log('Обнаружение утечки по розетке #' . $data->id);
    }


    public function ErrorLock($data)
    {
        $this->log('Обнаружение ошибки закрытия замка по розетке с пилотом #' . $data->id);
    }

    public function ErrorPp($data)
    {
        $this->log('Обнаружение ошибки кодирующего резистора по розетке с пилотом #' . $data->id);
    }

    public function Block($data)
    {
        $this->log('Успешная обработка комманды о блокировке розетки с пилотом #' . $data->id);
    }

    public function Unblock($data)
    {
        $this->log('Успешноая обработка комманды об окончании блокировке розетки с пилотом #' . $data->id);
    }

    public function Battery()
    {
        $this->log('Переход на батарейное питание');
    }

    public function Drop()
    {
        $this->log('Обрыв питающих фаз');
    }

    /** Пакет на остановку заряда:
     *  {"model":"station","action":"station_stop_plug","data":{"station":"000002", "plug": 2}}
     * @param $data
     * @return array
     */
    public static function stationStopPlug($data)
    {
        $response = [
            'client_id' => $data->station,
            'message' => self::prepareMessage('STOP::'.$data->plug)
        ];

        return $response;
    }

    /** Обновление карты - отправляется всем
     *
     * @param $data
     * @return array
     */
    public static function updateCart($data)
    {
        $response = [
            'message' => self::prepareMessage('EDIT:НОМЕР КАРТЫ:КРЕДИТ:АДМИН')
        ];

        return $response;
    }

    private static function prepareMessage($message)
    {
        return $message.'/r/n';
    }

    private function log($mess){
        $log = new \Log();
        $message = $this->station->getMessageId();
        $message .= $mess;
        $log->add(__CLASS__, $message);

    }

}