<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 15.11.16
 * Time: 23:20
 */

namespace Model;

include_once '../models/CardRepository.php';
include_once '../models/UserRepository.php';
include_once '../models/StationRepository.php';
include_once '../Entity/User.php';
include_once '../Entity/Card.php';
include_once '../Entity/Station.php';
include_once '../Entity/Plug.php';

class Card
{

    /**
     * @var \Entity\Card
     */
    private $card;

    /**
     * @param $cardId
     * @return $this
     */
    public function setCard($cardId)
    {
        $card = new \Entity\Card();
        $cardData = new \Model\CardRepository();
        $cardData = $cardData->findById((int) $cardId);
        $userData = new \Model\UserRepository();
        $userData = $userData->findById($cardData['user']);
        $isAdmin = $userData['roles'][0] == 'admin' ? 1 : 0;
        $card
            ->setId($cardData['id'])
            ->setUser(
                (new \Entity\User())
                    ->setId($userData['_id'])
                    ->setBalance($userData['balance'])
                    ->setIsAdmin($isAdmin)

            );

        $this->card = $card;
        return $this;
    }

    /**
     * @return \Entity\Card
     */
    public function getCard()
    {
        return $this->card;
    }
}