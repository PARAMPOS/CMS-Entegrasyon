<?php
/**
 * Created by Payfull.
 * Date: 10/18/2018
 */

namespace param\paramBasics;

class BIN_SanalPos
{
    public $G;//control and security object
    public $BIN;//CArd BIN

    /**
     * BIN_SanalPos constructor.
     * @param $BIN: card bin
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     */
    public function __construct($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $BIN)
    {
        $this->BIN = $BIN;
        $this->G = new G($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD);
    }
}