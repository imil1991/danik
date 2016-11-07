<?php
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 07.11.16
 * Time: 18:05
 */

namespace Entity;


class Plug
{
    const STATUS_OPEN = 0;
    const STATUS_CLOSE = 1;
    const STATUS_BUSY = 2;

    /**
     * @var array
     */
    private $plugs;

    public function getPlugs()
    {
        return $this->plugs;
    }

    /**
     * @param array $plugs
     * @return $this
     */
    public function setPlugs($plugs)
    {
        $this->plugs = $plugs;

        return $this;
    }

    /**
     * @param $id
     * @param $status
     * @return $this
     */
    public function setPlugStatus($id, $status)
    {
        $this->plugs[$id] = $status;

        return $this;
    }

}