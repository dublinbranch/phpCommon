<?php

function logger1($path, $msg){
	file_put_contents($path, date("Y-m-d H:i:s") . "\n" . $msg . "\n--------------------------------------------------------------------------------------------\n", FILE_APPEND | LOCK_EX);
}