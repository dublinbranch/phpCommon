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
if (!function_exists("clickHouseHandler")) {
    function clickHouseHandler()
    {
    }

    class ClickHouse
    {
        private string $remote;
        private $ch;
        public ?string $errorMsg = null;

        public function ClickHouse(string $remote)
        {
            $this->remote = $remote;
            $this->ch = curl_init($remote);
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_HEADER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_VERBOSE, 0);

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