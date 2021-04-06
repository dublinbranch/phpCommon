<?php
//ASSOLUTAMENTE QUESTO FILE DEVE ESSERE STAND ALONE, OGNI DIPENDENZA PUÒ ESSER BUGGA E SCASSARLO

function sendToSlack(string $txt, object $config): void
{
    if (strlen($txt) == 0) {
        return;
    }
    $data = array(
        "text" => $config->who . " " . date("Y-m-dTH:i:s") . " {$txt}\n",
        "channel" => $config->channel,
        "username" => $config->username
    );
    $curl = curl_init($config->url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_exec($curl);
    curl_close($curl);
}

function errorFilter(&$errorMessage): void
{
    $excludeds = array(
        "file_get_contents(https://www.bingapis.com/api/V7/ads/adsimpressionfeedback",
        "file_get_contents(): Failed to enable crypto",
        "file_get_contents(): SSL: Connection reset by peer in",
        "file_get_contents(https://www.bingapis.com/api/ping"
    );
    foreach ($excludeds as $excluded) {
        if (stripos($errorMessage, $excluded) !== false) {
            $errorMessage = "";
            break;
        }
    }
}

function handleError(?array $error): void
{
    if (isset($error["type"])) {
        $config = json_decode(file_get_contents(__DIR__ . "/config.json"));
        if ($config->active) {
            errorFilter($error["message"]);
            $key = 'errorTrackerTS';
            $lastTS = apcu_fetch($key);
            $difference = time() - $lastTS;
            if (strlen($error["message"]) > 0 && (!$lastTS || $difference > $config->TTL)) {
                apcu_store($key, time());
                $f = $_SERVER["SCRIPT_FILENAME"];
		$msg = "{$error['message']} in {$error['file']}:{$error['line']} \n For page " . $_SERVER["REQUEST_URI"];
                mail($config->mail, "FATAL PHP ERROR", "calling $f " . $msg);
                sendToSlack("calling $f " . $msg, $config);
            }
        }
    }
}



register_shutdown_function(function () {
    $error = error_get_last();
    handleError($error);
});
