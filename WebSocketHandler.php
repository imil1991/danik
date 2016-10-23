<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 23.10.16
 * Time: 13:36
 */

namespace demon;
require_once 'WebSocketWorker.php';
use demon\WebSocketWorker;

class WebSocketHandler extends WebSocketWorker{

    /** вызывается при соединении с новым клиентом
     * @param $client
     * @param $info
     */
    protected function onOpen($client, $info)
    {
    }

    /** вызывается при закрытии соединения клиентом
     * @param $client
     */
    protected function onClose($client)
    {

    }

    /** вызывается при получении сообщения от клиента
     * @param $client
     * @param $data
     */
    protected function onMessage($client, $data)
    {
        $data = $this->decode($data);

        if (!$data['payload']) {
            return;
        }

        if (!mb_check_encoding($data['payload'], 'utf-8')) {
            return;
        }

        # шлем всем сообщение, о том, что пишет один из клиентов
        $message = 'пользователь #' . intval($client) . ' (' . $this->pid . '): ' . strip_tags($data['payload']);
        $this->send($message);
        $this->sendHelper($message);
    }

    /** вызывается при получении сообщения от мастера
     * @param $data
     */
    protected function onSend($data)
    {
        $this->sendHelper($data);
    }

    /** отправляем сообщение на мастер, чтобы он разослал его на все воркеры
     * @param $message
     */
    protected function send($message)
    {
        @fwrite($this->master, $message);
    }

    private function sendHelper($data)
    {
        $data = $this->encode($data);
        $write = $this->clients;
        if (stream_select($read, $write, $except, 0)) {
            foreach ($write as $client) {
                @fwrite($client, $data);
            }
        }
    }
}