<?php
/**
 * Created by Payfull.
 * Date: 10/15/2018
 */

namespace param;

class Config 
{
    public $mode;//TEST or something else
    public $clientCode;//Terminal ID, It will be forwarded by param.
    public $clientUsername;//User Name, It will be forwarded by param.
    public $clientPassword;//Password, It will be forwarded by param.
    public $guid;//Key Belonging to Member Workplace
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
