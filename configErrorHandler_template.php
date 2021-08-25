<?php

$slackConfig = [];

class ErrorHandlerConfig
{
    public bool $active = true;
    public string $email;
    public string $url = "https://hooks.slack.com/services/";
    public int $ttl = 120;
    public string $who = "";
    public string $channel = "";
    public string $username = "";

}
$slackConfig["hard"] = new ErrorHandlerConfig();

$developTest = new ErrorHandlerConfig();
$developTest->channel = "develop_test";
$developTest->who = "";

$slackConfig["developTest"] = $developTest;
unset($developTest);
