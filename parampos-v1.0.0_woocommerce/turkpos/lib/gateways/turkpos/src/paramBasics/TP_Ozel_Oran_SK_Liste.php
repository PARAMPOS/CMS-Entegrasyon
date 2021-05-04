<?php
/**
 * Created by Payfull.
 * Date: 14/1/2019
 */

namespace param\paramBasics;

class TP_Ozel_Oran_SK_Liste
{
    public $GUID;//Key Belonging to Member Workplace
    public $G;//control and security object

    /**
     * TP_Ozel_Oran_Liste constructor.
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     */
    public function __construct($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD, $guid)
    {
        $this->GUID = $guid;
        $this->G = new G($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD);
    }
}