<?php
class TotalSpecialRatio
{

    function __construct($guid)
    {
        global $env;

        $this->GUID = $guid;
        $this->G = new GeneralClass($env['CLIENT_CODE'], $env['CLIENT_USERNAME'], $env['CLIENT_PASSWORD']);

    }
}