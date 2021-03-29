<?php
function ensureSingleRun(string $filename) : void
{
    //THIS LINE MUST BE KEPT! else the fp will go out of scope and be auto closed!
    GLOBAL $fp;
    $file = __DIR__ . "/$filename";
    $fp = fopen($file, "c+");
    if (!$fp) {
        die("impossible to open LOCK file: " . $file);
    }
    $locked = flock($fp, LOCK_EX | LOCK_NB);
    if (!$locked) {  // acquire an exclusive lock
        die("Couldn't get the lock!");
    }
}
