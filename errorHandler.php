<?php
//ASSOLUTAMENTE QUESTO FILE DEVE ESSERE STAND ALONE, OGNI DIPENDENZA PUÒ ESSER BUGGA E SCASSARLO

if (!function_exists("dummyErrorHandler")) {
    function dummyErrorHandlerFunkz()
    {
    }

    //This will load an array like
    //$slackConfig["hard"] ...
    //$slackConfig["developTest"] ...
    require_once(__DIR__ . "/configErrorHandler.php");
}

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


function sendToSlackApcu(string $message, string $apcuKey, ErrorHandlerConfig $config): void
{
    if (!apcu_exists($apcuKey)) {
        apcu_store($apcuKey, true, $config->ttl);
        sendToSlack($message, $config);
    }
}

function invalidError($errorMessage): bool
{
    $excludeds = array(
        "file_get_contents(https://www.bingapis.com/api/V7/ads/adsimpressionfeedback",
        "file_get_contents(): Failed to enable crypto",
        "file_get_contents(): SSL: Connection reset by peer in",
        "file_get_contents(https://www.bingapis.com/api/ping",
        "pinocchio/geppetto",
        "https://askprivate.com/postback?cid",
        "http://aj2284.online/at?subId=",
        "http://eu.rollerads.com/conversion/",
        "techAdsRepanel2",
        "/repanel/",
        "/pinocchio",
        "carlo.php",
        "offers.cfactory.affise.com/postback?clickid",
        "eu.rollerads.com/conversion",
        "postback.zeroredirect1.com/zppostback",
        "trc.taboola.com/actions-handler",
        "/seek?q=ciao%20bruno",
        "swiftlinux.com",
        "/ar?gclid=&layout=&mkt=&o=&q=&rtb=",
        "adminer_plugins",
	"piwik2.trott",
	"clickHouse/adminer",
	"ad.atdmt.com",
	"matomo"
    );
    foreach ($excludeds as $excluded) {
        if (stripos($errorMessage, $excluded) !== false) {
            return true;
        }
    }
    return false;
}

function invalidReferer(?string $refer): bool
{
    if (!$refer) {
        return false;
    }
    $excludeds = array(
        "swiftlinux.com"
    );
    foreach ($excludeds as $excluded) {
        if (stripos($refer, $excluded) !== false) {
            return true;
        }
    }
    return false;
}


function handleError(?array $error): void
{
    if (isset($error["type"])) {
        global $slackConfig;
        $config = $slackConfig["hard"];
        if ($config->active) {

            if (invalidError($error["message"])) {
                return;
            }
            if (invalidError($_SERVER["SCRIPT_FILENAME"])) {
                return;
            }
            if (invalidReferer(@$_SERVER['HTTP_REFERER'])) {
                return;
            }

            $key = 'errorTrackerTS';
            $lastTS = apcu_fetch($key);
            $difference = time() - $lastTS;
            if (strlen($error["message"]) > 0 && (!$lastTS || $difference > $config->ttl)) {
                apcu_store($key, time());
                $f = $_SERVER["SCRIPT_FILENAME"];
                $ref = isset($_SERVER['HTTP_REFERER']) ? ("and referrer " . $_SERVER['HTTP_REFERER']) : NULL;
                $msg = "{$error['message']} in {$error['file']}:{$error['line']} \n For page " . $_SERVER["REQUEST_URI"] . $ref;
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
