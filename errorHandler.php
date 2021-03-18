<?php
	//ASSOLUTAMENTE QUESTO FILE DEVE ESSERE STAND ALONE, OGIN DIPENDENZA PUÒ ESSER BUGGA E SCASSARLO

    function sendToSlack(string $txt, $config): void
    {
        if (strlen($txt) == 0) {
            return;
        }
	$data = array(
	"text" => $config->who . " " . date("Y-m-dTH:i:s") . " {$txt}\n",
	"channel" => $config->channel,
	"username" => $config->username
	);
	$curl = curl_init( $config->url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$a = curl_exec($curl);
	curl_close($curl);
    }

register_shutdown_function(function () {
    $error = error_get_last();
    /*
    filrtare via 
                            "pattern" : [
                        "PHP Warning:  file_get_contents(https://www.bingapis.com/api/V7/ads/adsimpressionfeedback"
                        ,"PHP Warning:  file_get_contents(): Failed to enable crypto"
                        ,"PHP Warning:  file_get_contents(): SSL: Connection reset by peer in"
                        ,""
                        ]

    */
    if (isset($error["type"])){ // && ($error["type"] == E_ERROR || $error["type"] == E_COMPILE_ERROR || $error["type"] == 4) ) {
	$config = json_decode(file_get_contents("/srv/www/phpCommon/config.json"));
        if ($config->active) {
            $key = 'errorTrackerTS';
            $lastTS = apcu_fetch($key);
            $difference = time() - $lastTS;
            if (!$lastTS || $difference > $config->TTL) {
                apcu_store($key, time());
		$f = $_SERVER["SCRIPT_FILENAME"];
                mail($config->mail, "FATAL PHP ERROR", "calling $f " . $error["message"]);
                sendToSlack("calling $f " .  $error["message"], $config);
            }
        }
    }
});
