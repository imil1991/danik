<?php
namespace Model;
use Entity\Plug;
require_once 'Entity/Card.php';
require_once 'Entity/Plug.php';
require_once 'Entity/Station.php';
require_once 'Entity/User.php';
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 27.10.16
 * Time: 14:51
 */
class Station {

    private $station;

    public function __construct($id)
    {
        $this->station = new \Entity\Station;
        $this->station->setId($id);
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
            'message' => self::prepareMessage($state.'::'.$data->plug)
        ];

        return $response;
    }


    /** При обнаружении карты клиента
     * {"model":"station","action":"card_found","data":{"id":"0x630x630xA90xBB"}}
     * @param $data
     * @return mixed
     */
    public static function CardFound($data)
    {
        $parse = unpack('C*',$data->id);
        $cardInfo = ['balance' => 100, 'admin' => 1 ];
        $pack = pack('C*', $cardInfo['balance']);
        $response['message'] = 'CARD'.$data->id. $pack .$cardInfo['admin'].'\r\n';

        return $response;

    }

    /** При начале потребления тока по розеткам
     * @param $data
     * @return array
     */
    public function Start($data)
    {
        $this->station->setPlugs(
            (new Plug)
                ->setPlugStatus($data->id, Plug::STATUS_BUSY)
        );
        $card = $this->station->getCard();
        $user = $card->getUser();
        $newBalance = $user->getBalance() - 1;
        $user->setBalance($newBalance);
        $response = [
            'message' => self::prepareMessage('EDIT'.$card->getCode().$newBalance.$user->getIsAdmin()),
            'client' => 'all'
        ];

        return $response;
    }

    /** При окончании потребления тока по розеткам
     * @param $data
     */
    public function Stop($data)
    {
        $this->station->setPlugs(
            (new Plug)
                ->setPlugStatus($data->id, Plug::STATUS_OPEN)
        );
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