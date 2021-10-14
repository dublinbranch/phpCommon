<?php

function logger1($path, $msg){
	$res = @file_put_contents($path, date("Y-m-d H:i:s") . "\n" . $msg . "\n--------------------------------------------------------------------------------------------\n", FILE_APPEND | LOCK_EX);
	if(!$res){
        echo "impossible salvare in $path";
        //$processUser = posix_getpwuid(posix_geteuid());
        //print_r($processUser);
	}
	

}
