#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: bona
 * Date: 11.11.16
 * Time: 15:39
 */

$fp = fsockopen("tcp://0.0.0.0", 8000, $errno, $errstr);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    $out = '{"model":"station","action":"set_id","data":{"id":"master"}}';
    fwrite($fp, $out);
    while (!feof($fp)) {
        $response = fgets($fp, 2);
        if($response == 'OK'){
            $out = '{"model":"station","action":"station_control","data":{"station":"1", "plug": 1, "status": true }}';
            fwrite($fp, $out);
        }
    }
    fclose($fp);
}
