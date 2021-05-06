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
    public int $tries = 1;
    public array $info;
    public int $code;
    public bool $dummy = false;
    public ?array $header = null;

    public function packDebugMsg(): string
    {
        if($this->dummy){
            return "";
        }
        $header = "";
        if ($this->header) {
            $header = "Header: " . print_r($this->header, true);
        }
        $msg = <<<EOD
For url: {$this->req->url}
Used Ip: {$this->info["primary_ip"]}  |  Response: {$this->info["http_code"]}
{$header}
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

    public function populate($ch)
    {
        $this->info = curl_getinfo($ch);
        $this->code = $this->info["http_code"];
        $this->error = curl_error($ch);
        $_2xx = ((int)$this->code / 100) == 2;
        if ($_2xx) {
            $this->ok = true;
        }
    }

    public static function fromCurl($ch, string $url): PingerRes
    {
        //Come si prende la url iniziale da curl ? bo io sò prenderla solo dopo i redirect
        $i = new PingerRes();
        $i->res = "1"; //Dummy value, so looks like was success full ?
        $i->req = new PingerReq($url);
        $i->populate($ch);

        return $i;
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

    for (; $res->tries < $req->maxTries; $res->tries++) {
        $r = curl_exec($ch);
        if ($r !== false) {
            $res->res = $r;
        }
        $res->populate($ch);
        if ($res->ok) {
            break;
        }
    }
    if (!$curl) {
        curl_close($ch);
    }
    return $res;
}
