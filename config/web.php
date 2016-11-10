<?php
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 27.10.16
 * Time: 14:15
 */

return [
    'route' => [
        'newcards' => 'station/updateCart',
        'stationcontrol' => 'station/control',
        'stop-plug' => 'station/stopPlug',
    ],
    'remote' => [
        'host' => '5.101.105.227',
        'login' => 'root',
        'password' => '[e1995b&&&a1996r]'
    ],
    'socket' =>[
        'host' => '0.0.0.0',
        'port' => 8000,
        'workers' => 1,
    ]
];