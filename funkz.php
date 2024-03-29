<?php
if (!function_exists("dummyPhpCommonFunkz")) {
    function dummyPhpCommonFunkz()
    {
    }

    function request_ifset(string $what, bool $numeric = false, $default = NULL)
    {
        if (isset($_REQUEST[$what])) {
            if ($numeric) {
                return (int)$_REQUEST[$what];
            }
            return $_REQUEST[$what];
        } else {
            if (!is_null($default)) {
                return $default;
            }
        }
        return null;
    }

    function request_ifsetMulti(array $elements, bool $numeric = false, $default = NULL)
    {
        foreach ($elements as $element) {
            $value = request_ifset($element, $numeric);
            if (!empty($value)) {
                return $value;
            }
        }
        return $default;
    }

    function request_requiredAny(array $elements, bool $numeric = false)
    {
        $res = request_ifsetMulti($elements, $numeric, NULL);
        if (is_null($res)) {
            die("Missing required parameter:" . print_r($elements));
        }
        return $res;
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
        } else if (isset($_REQUEST['REMOTE_ADDRESS'])) {
            $ip = $_REQUEST['REMOTE_ADDRESS'];
        } //The followings are HEADER, not parameter
        else if (getenv('HTTP_CLIENT_IP')) {
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

    function sessionSafeStart()
    {
        if (php_sapi_name() !== 'cli') {
            // header_sent because sometimes we have to open and close session
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_start();
            }
        }
    }


    function arrayIfSet($array, $key, $minSize = null)
    {
        if (isset ($array[$key])) {
            $value = $array[$key];
            if ($minSize) {
                if (strlen($value) < $minSize) {
                    return null;
                }
            }
            return $value;
        } else {
            return null;
        }
    }

    function arrayIfSetAny($array, $keys){
        foreach ($keys as $key){
            return arrayIfSet($array,$key);
        }
    }


    function base64FromUrlSafeB64(string $original): string
    {
        $original = str_replace("_", "/", $original);
        $original = str_replace("-", "+", $original);
        return $original;
    }

//quite slow, but is the only thing working, the trick of decoding re-encoding looks like can fail too, missing padding, invalid termination char and other edge cases
    function isValidBase64(string $coded1, &$message = null): bool
    {
        //https://en.wikipedia.org/wiki/ASCII
        /*
    BIN         OCT D   H
    010 0000	040	32	20	 space
    010 0001	041	33	21	!
    010 0010	042	34	22	"
    010 0011	043	35	23	#
    010 0100	044	36	24	$
    010 0101	045	37	25	%
    010 0110	046	38	26	&
    010 0111	047	39	27	'
    010 1000	050	40	28	(
    010 1001	051	41	29	)
    010 1010	052	42	2A	*
    010 1011	053	43	2B	+
    010 1100	054	44	2C	,
    010 1101	055	45	2D	-
    010 1110	056	46	2E	.
    010 1111	057	47	2F	/
    011 0000	060	48	30	0
    011 0001	061	49	31	1
    011 0010	062	50	32	2
    011 0011	063	51	33	3
    011 0100	064	52	34	4
    011 0101	065	53	35	5
    011 0110	066	54	36	6
    011 0111	067	55	37	7
    011 1000	070	56	38	8
    011 1001	071	57	39	9
    011 1010	072	58	3A	:
    011 1011	073	59	3B	;
    011 1100	074	60	3C	<
    011 1101	075	61	3D	=
    011 1110	076	62	3E	>
    011 1111	077	63	3F	?
    100 0000	100	64	40	@	`	@
    100 0001	101	65	41	A
    100 0010	102	66	42	B
    100 0011	103	67	43	C
    100 0100	104	68	44	D
    100 0101	105	69	45	E
    100 0110	106	70	46	F
    100 0111	107	71	47	G
    100 1000	110	72	48	H
    100 1001	111	73	49	I
    100 1010	112	74	4A	J
    100 1011	113	75	4B	K
    100 1100	114	76	4C	L
    100 1101	115	77	4D	M
    100 1110	116	78	4E	N
    100 1111	117	79	4F	O
    101 0000	120	80	50	P
    101 0001	121	81	51	Q
    101 0010	122	82	52	R
    101 0011	123	83	53	S
    101 0100	124	84	54	T
    101 0101	125	85	55	U
    101 0110	126	86	56	V
    101 0111	127	87	57	W
    101 1000	130	88	58	X
    101 1001	131	89	59	Y
    101 1010	132	90	5A	Z
    101 1011	133	91	5B	[
    101 1100	134	92	5C	\	~	\
    101 1101	135	93	5D	]
    101 1110	136	94	5E	↑	^
    101 1111	137	95	5F	←	_
    110 0000	140	96	60		@	`
    110 0001	141	97	61		a
    110 0010	142	98	62		b
    110 0011	143	99	63		c
    110 0100	144	100	64		d
    110 0101	145	101	65		e
    110 0110	146	102	66		f
    110 0111	147	103	67		g
    110 1000	150	104	68		h
    110 1001	151	105	69		i
    110 1010	152	106	6A		j
    110 1011	153	107	6B		k
    110 1100	154	108	6C		l
    110 1101	155	109	6D		m
    110 1110	156	110	6E		n
    110 1111	157	111	6F		o
    111 0000	160	112	70		p
    111 0001	161	113	71		q
    111 0010	162	114	72		r
    111 0011	163	115	73		s
    111 0100	164	116	74		t
    111 0101	165	117	75		u
    111 0110	166	118	76		v
    111 0111	167	119	77		w
    111 1000	170	120	78		x
    111 1001	171	121	79		y
    111 1010	172	122	7A		z
    111 1011	173	123	7B		{
    111 1100	174	124	7C	ACK	¬	|
    111 1101	175	125	7D		}
    111 1110	176	126	7E	ESC	|	~
         */
        for ($i = 0;
             $i < strlen($coded1);
             $i++) {
            $char = $coded1[$i];
            $int = ord($char);

            if ($int > 47 && $int < 58) {
                continue;
            } elseif ($int > 64 && $int < 91) { //AZ
                continue;
            } elseif ($int > 96 && $int < 123) { //az
                continue;
            } else {
                switch ($int) {
                    case 43: //+
                    case 45: //-
                    case 47: // /
                    case 61: // =
                    case 95: // _
                        continue 2;
                        break;
                    default:
                    {
                        $message = "$char";
                        return false;
                    }
                }
            }
        }
        return true;
    }


    if (!function_exists('http_build_url')) {
        // Define constants
        define('HTTP_URL_REPLACE', 0x0001);    // Replace every part of the first URL when there's one of the second URL
        define('HTTP_URL_JOIN_PATH', 0x0002);    // Join relative paths
        define('HTTP_URL_JOIN_QUERY', 0x0004);    // Join query strings
        define('HTTP_URL_STRIP_USER', 0x0008);    // Strip any user authentication information
        define('HTTP_URL_STRIP_PASS', 0x0010);    // Strip any password authentication information
        define('HTTP_URL_STRIP_PORT', 0x0020);    // Strip explicit port numbers
        define('HTTP_URL_STRIP_PATH', 0x0040);    // Strip complete path
        define('HTTP_URL_STRIP_QUERY', 0x0080);    // Strip query string
        define('HTTP_URL_STRIP_FRAGMENT', 0x0100);    // Strip any fragments (#identifier)

        // Combination constants
        define('HTTP_URL_STRIP_AUTH', HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS);
        define('HTTP_URL_STRIP_ALL', HTTP_URL_STRIP_AUTH | HTTP_URL_STRIP_PORT | HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT);

        /**
         * HTTP Build URL
         * Combines arrays in the form of parse_url() into a new string based on specific options
         * @name http_build_url
         * @param string|array $url The existing URL as a string or result from parse_url
         * @param string|array $parts Same as $url
         * @param int $flags URLs are combined based on these
         * @param array &$new_url If set, filled with array version of new url
         * @return string
         */
        function http_build_url(/*string|array*/ $url, /*string|array*/ $parts = array(), /*int*/ $flags = HTTP_URL_REPLACE, /*array*/ &$new_url = false)
        {
            // If the $url is a string
            if (is_string($url)) {
                $url = parse_url($url);
            }

            // If the $parts is a string
            if (is_string($parts)) {
                $parts = parse_url($parts);
            }

            // Scheme and Host are always replaced
            if (isset($parts['scheme'])) {
                $url['scheme'] = $parts['scheme'];
            }
            if (isset($parts['host'])) {
                $url['host'] = $parts['host'];
            }

            // (If applicable) Replace the original URL with it's new parts
            if (HTTP_URL_REPLACE & $flags) {
                // Go through each possible key
                foreach (array('user', 'pass', 'port', 'path', 'query', 'fragment') as $key) {
                    // If it's set in $parts, replace it in $url
                    if (isset($parts[$key])) {
                        $url[$key] = $parts[$key];
                    }
                }
            } else {
                // Join the original URL path with the new path
                if (isset($parts['path']) && (HTTP_URL_JOIN_PATH & $flags)) {
                    if (isset($url['path']) && $url['path'] != '') {
                        // If the URL doesn't start with a slash, we need to merge
                        if ($url['path'][0] != '/') {
                            // If the path ends with a slash, store as is
                            if ('/' == $parts['path'][strlen($parts['path']) - 1]) {
                                $sBasePath = $parts['path'];
                            } // Else trim off the file
                            else {
                                // Get just the base directory
                                $sBasePath = dirname($parts['path']);
                            }

                            // If it's empty
                            if ('' == $sBasePath) {
                                $sBasePath = '/';
                            }

                            // Add the two together
                            $url['path'] = $sBasePath . $url['path'];

                            // Free memory
                            unset($sBasePath);
                        }

                        if (false !== strpos($url['path'], './')) {
                            // Remove any '../' and their directories
                            while (preg_match('/\w+\/\.\.\//', $url['path'])) {
                                $url['path'] = preg_replace('/\w+\/\.\.\//', '', $url['path']);
                            }

                            // Remove any './'
                            $url['path'] = str_replace('./', '', $url['path']);
                        }
                    } else {
                        $url['path'] = $parts['path'];
                    }
                }

                // Join the original query string with the new query string
                if (isset($parts['query']) && (HTTP_URL_JOIN_QUERY & $flags)) {
                    if (isset($url['query'])) {
                        $url['query'] .= '&' . $parts['query'];
                    } else {
                        $url['query'] = $parts['query'];
                    }
                }
            }

            // Strips all the applicable sections of the URL
            if (HTTP_URL_STRIP_USER & $flags) {
                unset($url['user']);
            }
            if (HTTP_URL_STRIP_PASS & $flags) {
                unset($url['pass']);
            }
            if (HTTP_URL_STRIP_PORT & $flags) {
                unset($url['port']);
            }
            if (HTTP_URL_STRIP_PATH & $flags) {
                unset($url['path']);
            }
            if (HTTP_URL_STRIP_QUERY & $flags) {
                unset($url['query']);
            }
            if (HTTP_URL_STRIP_FRAGMENT & $flags) {
                unset($url['fragment']);
            }

            // Store the new associative array in $new_url
            $new_url = $url;

            // Combine the new elements into a string and return it
            return
                ((isset($url['scheme'])) ? $url['scheme'] . '://' : '')
                . ((isset($url['user'])) ? $url['user'] . ((isset($url['pass'])) ? ':' . $url['pass'] : '') . '@' : '')
                . ((isset($url['host'])) ? $url['host'] : '')
                . ((isset($url['port'])) ? ':' . $url['port'] : '')
                . ((isset($url['path'])) ? $url['path'] : '')
                . ((isset($url['query'])) ? '?' . $url['query'] : '')
                . ((isset($url['fragment'])) ? '#' . $url['fragment'] : '');
        }
    }

    function hasRefUrl(): int
    {
        $hasRefUrlGet = isset($_GET["refUrl"]) && strlen($_GET["refUrl"]);
        $hasRefUrlServer = isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"]) ? 1 : 0;
        return $hasRefUrlServer || $hasRefUrlGet;
    }

    // Format must be 00:00 => 23:59 https://stackoverflow.com/questions/27131527/php-check-if-time-is-between-two-times-regardless-of-date/27134087
    function isInTimeRange_Hi(string $from, string $to, string $now, string $timezone): bool
    {
        $tz = new DateTimeZone($timezone);
        $from = DateTime::createFromFormat('!H:i', $from, $tz);
        $to = DateTime::createFromFormat('!H:i', $to, $tz);
        $now = DateTime::createFromFormat('!H:i', $now, $tz);
        if ($from > $to) {
            $to->modify('+1 day');
        }
        $result = ($from <= $now && $now <= $to) || ($from <= $now->modify('+1 day') && $now <= $to);
        return $result;
    }

    function getInformationSchema(DBWrapper $db, string $tableName = "", string $databaseName = "") : array
    {
        if(empty($tableName) && empty($databaseName)){
            throw new Exception('table name or database name must be defined');
        }
        $skel = <<<SQL
SELECT *
FROM information_schema.tables
WHERE 
SQL;
        $wheres = array();
        $values = array();
        if(!empty($tableName)){
            $wheres[] = 'table_name = %s';
            $values[] = base64this($tableName);
        }
        if(!empty($databaseName)){
            $wheres[] = 'table_schema = %s';
            $values[] = base64this($databaseName);
        }
        $skel .= implode(' AND ', $wheres);
        $query = vsprintf($skel,$values);
        $result = $db->getAllObj($query);
        return $result;
    }

    function doesTableExist(DBWrapper $db, string $tableName, string $databaseName) : bool
    {
        $info = getInformationSchema($db, $tableName, $databaseName);
        return sizeof($info) > 0;
    }

    function getLastTableUpdateOrCreateTs(DBWrapper $db, string $tableName, string $databaseName)
    {
        $info = getInformationSchema($db, $tableName, $databaseName);
        if(!isset($info[0]) && (!isset($info[0]->UPDATE_TIME) || !isset($info[0]->CREATE_TIME))){
            throw new Exception('unable to get infos');
        }
        if(isset($info[0]->UPDATE_TIME)) {
            $ts = strtotime($info[0]->UPDATE_TIME);
        }elseif(isset($info[0]->CREATE_TIME)) {
            $ts = strtotime($info[0]->CREATE_TIME);
        }
        return $ts;
    }

}//End of includeGuard

