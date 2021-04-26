<?php
if (!function_exists("dummyPhpCommonFunkz")) {
    function dummyPhpCommonFunkz()
    {
    }

    function request_ifset(string $what, bool $numeric = false, $default = NULL)
    {

        if ($numeric && !isset($_REQUEST[$what])) {
            return $default;
        }

        if ($numeric && !is_numeric($_REQUEST[$what])) {
            return $default;
        }

        if (isset($_REQUEST[$what])) {
            if ($numeric) {
                return (int)$_REQUEST[$what];
            }
            return $_REQUEST[$what];
        }

        if ($default) {
            return $default;
        }

        return false;
    }

    function request_required(string $what, bool $numeric = false)
    {
        $res = request_ifset($what, $numeric, null);
        if (is_null($res)) {
            die("Missing required parameter: {$what}");
        }
        return $res;
    }

    function request_requiredMulti(array $elements): array
    {
        $values = array();
        foreach ($elements as $element) {
            $values[$element["what"]] = request_required($element["what"], $element["numeric"]);
        }
        return $values;
    }

    function getRequestIp(): string
    {
        return request_ifset('ip', false, getIp());
    }

    function getIp(): string
    {
        if (isset($_REQUEST['ip']) && filter_var($_REQUEST['ip'], FILTER_VALIDATE_IP)) {
            $ip = $_REQUEST['ip'];
            //The followings are HEADER, not parameter
        } else if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = '';
        }
        //In case of multiple forwarding
        if (strpos($ip, ",") !== false) {
            return explode(",", $ip)[0];
        }
        return $ip;
    }

    function getUserAgent(): string
    {
        if (isset($_REQUEST['HTTP_USER_AGENT'])) {
            return $_REQUEST['HTTP_USER_AGENT'];
        } else {
            return $_SERVER["HTTP_USER_AGENT"] ?? '';
        }
    }


    //Nginx rewrite are a bit funky sometimes
    function isExpectedPage(string $expected): bool
    {
        $le = strlen($expected);
        $sub = substr($_SERVER["REQUEST_URI"], 0, $le);
        return $sub == $expected;
    }

    function isDefinedAndTrue(string $const): bool
    {
        return defined($const) && constant($const);
    }

    function definedEqualTo(string $var, $value): bool
    {
        $defined = defined($var);
        $equal = false;
        if ($defined) {
            $equal = constant($var) === $value;
        }
        $res = $defined && $equal;
        return $res;
    }
}
