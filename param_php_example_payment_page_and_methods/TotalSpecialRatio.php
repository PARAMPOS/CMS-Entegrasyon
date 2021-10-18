<?php
class TotalSpecialRatio
{

    function __construct($globallyUniqueIdentifier)
    {
        global $env;
        $this->GUID = $globallyUniqueIdentifier;
        $this->G = new GeneralClass($env['CLIENT_CODE'], $env['CLIENT_USERNAME'], $env['CLIENT_PASSWORD']);

    }
}