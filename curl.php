<?php

class PingerReq
{
    function __construct(string $url, int $maxTries = 5)
    {
        $this->url = $url;
        $this->maxTries = $maxTries;
    }

    public string $url;
    public int $maxTries = 5;
}

class PingerRes
{
    public PingerReq $req;
    public bool $ok = false;
    public ?string $res = null;
    public ?string $error = null;
    public int $tries;
    public array $info;
    public int $code;

    public function packDebugMsg(): string
    {
        $msg = <<<EOD
For url: {$this->req->url}
Used Ip: {$this->info["primary_ip"]}  |  Response: {$this->info["http_code"]}
EOD;

        if (!$this->ok) {
            $msg .= "\nFailure after: {$this->tries}, error is: $this->error";
        }
        $msg .= "\n" . $this->getTiming();
        return $msg;

    }

    public function getTiming(): string
    {
        $msg = <<<EOD
Timing   
    namelookup_time => {$this->info["namelookup_time"]}
    connect_time => {$this->info["connect_time"]}
    pretransfer_time => {$this->info["pretransfer_time"]}
    starttransfer_time => {$this->info["starttransfer_time"]}
    total_time => {$this->info["total_time"]}
EOD;

        return $msg;
    }
}


function pinger1(PingerReq $req, $curl = null): PingerRes
{
    $res = new PingerRes();
    $res->req = $req;
    if ($curl) {
        $ch = $curl;
    } else {
        $ch = curl_init();
    }

    curl_setopt($ch, CURLOPT_URL, $req->url);
    //else will print the res -.-
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    //else will start from 0
    $res->tries = 1;
    for (; $res->tries < $req->maxTries; $res->tries++) {
        $r = curl_exec($ch);
        if ($r !== false) {
            $res->res = $r;
        }
        $res->info = curl_getinfo($ch);
        $res->code = $res->info["http_code"];
        $_2xx = ((int)$res->code / 100) == 2;
        if ($res && $_2xx) {
            $res->ok = true;
            break;
        }
        $res->error = curl_error($ch);
    }
    if (!$curl) {
        curl_close($ch);
    }
    return $res;
}