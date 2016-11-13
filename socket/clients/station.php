#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 11.11.16
 * Time: 15:39
 */

$fp = fsockopen("tcp://0.0.0.0", 8000, $errno, $errstr);
print_r($argv);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    $out = '{"model":"station","action":"set_id","data":{"id":"'.$argv[1].'"}}';
    fwrite($fp, $out);

}
