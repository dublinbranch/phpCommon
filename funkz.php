<?php
if (!function_exists("dummyPhpCommonFunkz")) {
    function dummyPhpCommonFunkz()
    {
    }

    function request_ifset(string $what, bool $numeric = false, $default = NULL): ?string
    {

        if ($numeric && !isset($_REQUEST[$what])) {
            return $default;
        }

        if ($numeric && !is_numeric($_REQUEST[$what])) {
            return $default;
        }

        if (isset($_REQUEST[$what])) {
            if ($_REQUEST[$what] === 0 || $_REQUEST[$what] == '0') {
                return ( int )0;
            } else {
                return $_REQUEST[$what];
            }
        }

        if ($default) {
            return $default;
        }

        return false;
    }

    function request_required(string $what, bool $numeric = false): string
    {
        $res = request_ifset($what, $numeric, false);
        if (!$res) {
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
        return $ip;
    }

    function getUserAgent(): string
    {
        return $_SERVER["HTTP_USER_AGENT"] ?? '';
    }

    function isExpectedPage(string $expected) : bool{
        $le = strlen($expected);
        $sub = substr($_SERVER["REQUEST_URI"],0, $le );
        return $sub == $expected;
    }

}
