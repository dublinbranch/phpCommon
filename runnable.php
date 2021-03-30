<?php

function runnable(string $key, int $second, bool $log = true)
{
    $db = DBS7();
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
            logRun($key, $now);
        }
        return true;
    }
    return false;
}


function logRun(string $key, int $now, DBWrapper $db)
{
    $db = DBS7();
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
