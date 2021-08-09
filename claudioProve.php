<?php
require_once "curl.php";

$req = new PingerReq("seisho.us/ip.php",3);
// 3 seconds curl timeout
$curlOpts = array(
    CURLOPT_TIMEOUT => 3
);
$curlOpts2 = array(
    CURLOPT_RANGE => 11
);
//$curl = curl_init();

$res = pinger1($req, null, $curlOpts2);
//$res = pinger1($req);