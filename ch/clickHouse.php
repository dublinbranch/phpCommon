<?php
/*
Come usare la classe ?

$ch = new ClickHouse("http://127.0.0.1:8123");
$res = $ch->query("SELECT today()");
if (!$res) {
    var_dump($ch->errorMsg);
    die();
}
 */
//TODO aggiungere le opzioni per loggare on error 
if (!function_exists("clickHouseHandler")) {
    function clickHouseHandler()
    {
    }

    class ClickHouse
    {
        private string $remote;
        private $ch;
	public ?string $errorMsg = null;
	//It depends a lot on what you are doing, running a select ? ok increase, doing massive amount of insert keep as low as possible
	//we just pool them togheter so better to be quick in case is having problem and processing remains struct
	public float $timeoutMS = 1000;

        public function ClickHouse(string $remote)
        {
            $this->remote = $remote;
            $this->ch = curl_init($remote);
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_HEADER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($this->ch, CURLOPT_VERBOSE, 0);
	    curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $this->timeoutMS);
        }

        public function query($sql): ?string
        {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $sql);
            $response = curl_exec($this->ch);
            if (curl_errno($this->ch)) {
                $this->errorMsg = curl_error($this->ch);
                return null;
            }
            if (strpos($response, "Poco::Exception")) {
                $this->errorMsg = $response;
                return null;
            }
            return $response;
        }
    }


}
