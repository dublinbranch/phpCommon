<?php

require_once __DIR__ . '/phpMysql/dbwrapper.php';

function DBS7() : DBWrapper{
    static $db = null;
    if( ! $db ){
        $db7 = new DBConf;
        $db7->db = "";
        $db7->host = "";
        $db7->port = 3306;
        $db7->passwd = "";
        $db7->user = "";
        $db = new DBWrapper( $db7 );
    }
    return $db;
}
