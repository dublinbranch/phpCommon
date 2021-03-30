<?php

require_once(__DIR__ . '/phpMysql/dbwrapper.php');

function runnable(string $key, int $second, DBWrapper $db, bool $log = true)
{
    $now = time();
    $skel = <<<EOD
SELECT
    id,
    lastRun
FROM
    maintenance.timing
WHERE
    operationCode = %s
ORDER BY
    lastRun DESC
LIMIT 1
EOD;
    $sql = sprintf($skel, base64this($key));
    $result = $db->getLine($sql);
    if (empty($result) || $result->lastRun + $second < $now) {
        if ($log) {
            logRun($key, $now, $db);
        }
        return true;
    }
    return false;
}


function logRun(string $key, int $now, DBWrapper $db)
{
    $skel = <<<EOD
INSERT INTO
    maintenance.timing
SET
    operationCode = %s,
    lastRun = %d
EOD;
    $sql = sprintf($skel, base64this($key), $now);
    $db->query($sql);
}
