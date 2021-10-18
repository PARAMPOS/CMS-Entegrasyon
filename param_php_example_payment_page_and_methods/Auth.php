<?php
class Auth
{
    function __construct($text)
    {
        global $env;
        $this->Data = $text;
        $this->G = new GeneralClass($env['CLIENT_CODE'], $env['CLIENT_USERNAME'], $env['CLIENT_PASSWORD']);
    }
}