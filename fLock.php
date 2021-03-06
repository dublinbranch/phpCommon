<?php
function ensureSingleRun(string $filename): void
{
    //THIS LINE MUST BE KEPT! else the fp will go out of scope and be auto closed!
    global $fp;
    $file = __DIR__ . "/$filename";
    $fp = fopen($file, "c+");
    if (!$fp) {
        die("impossible to open LOCK file: " . $file);
    }
    $locked = flock($fp, LOCK_EX | LOCK_NB);
    if (!$locked) {  // acquire an exclusive lock
        die("Couldn't get the lock!\n");
    }
}

class TechAdsFlock
{
    private string $filename;
    private bool $locked = false;
    private $fp;
    private bool $valid = false;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->fp = fopen($this->filename, "c+");
        if ($this->fp) {
            $this->valid = true;
        }else{
            die("Impossible to open / create the file {$this->filename}, does the folder exist ? Is accessible ? The folder need to be writable!");
        }
    }

    public function __destruct()
    {
        $this->unlock();
    }

    public function lock(): bool
    {
        if (!$this->locked) {
            $this->locked = flock($this->fp, LOCK_EX | LOCK_NB);
        }
        return $this->locked;
    }

    public function unlock(): bool
    {
        if ($this->locked) {
            $this->locked = flock($this->fp, LOCK_UN);
        }
        return $this->locked;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }
}
