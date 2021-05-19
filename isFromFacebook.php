<?php
require_once __DIR__ . "/apcSql.php";

class HasFbclick
{
    public ?string $fbclid = null;
    public bool $wrong = false;
    public bool $missing = false;
    public bool $valid = false;
    public ?string $error = null;
}

class IsFromFacebook
{
    public string $url;
    public ?array $urlQuery = null;
    public ?string $referral;
    public ?string $ua;


    public function __construct($url, $referral = null, $ua = null)
    {
        $this->url = $url;
        $this->referral = $referral;
        $this->ua = $ua;
    }

    public function decodeUrl(): array
    {
        if (!$this->urlQuery) {
            $query = parse_url($this->url, PHP_URL_QUERY);
            parse_str($query, $this->urlQuery);
        }
        return $this->urlQuery;
    }

    public function hasFbclick(): HasFbclick
    {
        $result = new HasFbclick();
        $query = $this->decodeUrl();

        //fbclid can be set but empty
        $result->fbclid = arrayIfSet($query, "fbclid", 10);
        if (!$result->fbclid) {
            $result->error = "missing parameter";
            $result->missing = true;
            return $result;
        }
        if (strlen($result->fbclid) < 55) {
            $result->error = "clickId too short";
            $result->wrong = true;
            return $result;
        }
        if (strlen($result->fbclid) > 220) { //max 197 atm
            $result->error = "clickId too long";
            $result->wrong = true;
            return $result;
        }

        $ok = isValidBase64($result->fbclid, $this->error);
        if (!$ok) {
            $result->wrong = true;
            return $result;
        }
        $result->valid = true;
        return $result;
    }

    //Used only for click for CBS s2s, as we do not have user agent, or refurl
    public function fromRange(): bool
    {
        $query = $this->decodeUrl();
        $au = arrayIfSet($query, "au", 2);
        $tt = arrayIfSet($query, "tt", 2);
        if ($au && $tt) {
            $sql = "SELECT rtyStart,rtyEnd FROM externalAgencies.ranges WHERE oCode = %d";
            $sql = sprintf($sql, $au);
            $res = apcCachedMulti($sql, DBS7(), 60);
            $ttInt = (int)str_replace("T", "", $tt);
            foreach ($res as $row) {
                $start = intval($row->rtyStart);
                $end = intval($row->rtyEnd);
                $a = $start <= $ttInt;
                $b = $end >= $ttInt;
                if ($a && $b) {
                    return true;
                }
            }
        }
        return false;
    }

    //for all other case is fine to check UA and Refurl
    public function fromFingerPrint(): bool
    {
        //I do not think is reasonable to use a full fledged UA detector for this
        if (strpos($this->ua, "[FBAN)") !== false) {
            return true;
        }
        if (strpos($this->referral, "facebook.com") !== false) {
            return true;
        }
        return false;
    }
}

