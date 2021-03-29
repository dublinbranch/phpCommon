<?php
function ensureSingleRun(string $filename) : void
{
    global $fp;
    $fp = fopen(__DIR__ . "/$filename", "c+");
    if (!$fp) {
        die("impossible to open LOCK file");
    }

    $locked = flock($fp, LOCK_EX | LOCK_NB);

    if (!$locked) {  // acquire an exclusive lock
        die("Couldn't get the lock!");
    }
}
