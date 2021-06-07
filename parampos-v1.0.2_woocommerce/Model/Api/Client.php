<?php

class Client 
{
    public $mode;
    public $clientCode;
    public $clientUsername;
    public $clientPassword;
    public $guid;
    public $serviceUrl;

    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl)
    {
        $this->clientCode = $clientCode;
        $this->clientUsername = $clientUsername;
        $this->clientPassword = $clientPassword;
        $this->guid = $guid;
        $this->mode = $mode;
        $this->serviceUrl = $serviceUrl;
    }
}
