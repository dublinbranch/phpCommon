<?php

require_once( __DIR__ . '/config.php' );

/** Heredoc Hack
 * @param $data
 * @return mixed
 */
$fn = function( $data ){ return $data; };

function request_ifset( string $what , bool $numeric = false , $default = 0 ) : ?string {
    $db = DBS7();

    if( $numeric && ! isset( $_REQUEST[$what] ) ){
        return $db->escape( $default );
    }

    if( $numeric && ! is_numeric( $_REQUEST[$what] ) ){
        return $db->escape( $default );
    }

    if( isset( $_REQUEST[$what] ) ){
        if( $_REQUEST[$what] === 0 || $_REQUEST[$what] == '0' ){
            return ( int ) 0;
        }else{
            return $db->escape( $_REQUEST[$what] );
        }
    }

    if( $default ){
        return $db->escape( $default );
    }

    return false;
}

function request_required( string $what , bool $numeric = false ) : string {
    $res = request_ifset( $what , $numeric , false );
    if( ! $res ){
        die( "Missing required parameter: {$what}" );
    }
    return $res;
}

function request_requiredMulti( array $elements ) : array {
    $values = array();
    foreach( $elements as $element ){
        $values[$element["what"]] = request_required( $element["what"] , $element["numeric"] );
    }
    return $values;
}

function getRequestIp() : string {
    return request_ifset( 'ip' , false , getIp() );
}

function getIp() : string {
    if( getenv( 'HTTP_CLIENT_IP' ) ) {
        $ip = getenv( 'HTTP_CLIENT_IP' );
    } else if( getenv( 'HTTP_X_FORWARDED_FOR' ) ){
        $ip = getenv( 'HTTP_X_FORWARDED_FOR' );
    } else if( getenv( 'HTTP_X_FORWARDED' ) ){
        $ip = getenv( 'HTTP_X_FORWARDED' );
    } else if( getenv( 'HTTP_FORWARDED_FOR' ) ){
        $ip = getenv( 'HTTP_FORWARDED_FOR' );
    } else if( getenv( 'HTTP_FORWARDED' ) ){
        $ip = getenv( 'HTTP_FORWARDED' );
    } else if( getenv( 'REMOTE_ADDR' ) ){
        $ip = getenv( 'REMOTE_ADDR' );
    } else {
        $ip = '';
    }
}

function getUserAgent() : string {
    return $_SERVER["HTTP_USER_AGENT"] ?? '';
}
