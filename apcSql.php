<?php
if (!function_exists("dummyApcSqlFunkz")) {
    function dummyApcSqlFunkz()
    {
    }
    //If we are here is guaranteed we already loaded the dbwrapper
    function apcCached(string $sql, DBWrapper $db, int $ttl = 60)
    {
        //we do not store boolean never, so this is ok, at most we have NULL...
        $res = apcu_fetch($sql);
        if ($res !== false) {
            return $res;
        }
        $res = $db->getLineSS($sql);
        apcu_store($sql,$res,$ttl);
        return $res;
    }

    //This return array<object>
    function apcCachedMulti(string $sql, DBWrapper $db, int $ttl = 60)
    {
        //we do not store boolean never, so this is ok, at most we have NULL...
        $res = apcu_fetch($sql);
        if ($res !== false) {
            return $res;
        }
        $sqlCopy = $sql;
        $res = $db->getAllObj($sql);
        apcu_store($sqlCopy,$res,$ttl);
        return $res;
    }
}