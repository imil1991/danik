<?php
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 07.11.16
 * Time: 19:16
 */

namespace Entity;


class User
{

    private $id;
    private $balance;
    private $is_admin;

    public function getId()
    {
        $this->id;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    public function getIsAdmin()
    {
        return $this->is_admin;
    }

    public function setIsAdmin($isAdmin)
    {
        $this->is_admin = $isAdmin;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    public function save(){
        return $this;
    }
}