<?php

class ErrorHandler {
    public static string $mail = "";
    public static int $TTL = 120;
    public static array $slackData = array(
        "url" => "" ,
        "who" => "" ,
        "channel" => "" ,
        "username" => ""
    );
    public static function isSetSlackData() : bool{
        return
            isset( self::$slackData["url"] )
            && isset( self::$slackData["who"] )
            && isset( self::$slackData["channel"] )
            && isset( self::$slackData["username"] );
    }
    public static function isSetMailTTL():bool{
        return
            isset( self::$mail )
            && isset( self::$TTL );
    }
}
