<?php

require_once("funkz.php");

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error["type"] == E_ERROR || $error["type"] == E_COMPILE_ERROR) {
        if (ErrorHandler::isSetMailTTL()) {
            $key = 'errorTrackerTS';
            $lastTS = apcu_fetch($key);
            $difference = time() - $lastTS;
            if (!$lastTS || $difference > ErrorHandler::$TTL) {
                apcu_store($key, time());
                mail(ErrorHandler::$mail, "FATAL PHP ERROR", $error["message"]);
                sendToSlack($error["message"]);
            }
        }
    }
});
