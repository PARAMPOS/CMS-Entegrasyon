<?php
/**
 * Created by Payfull.
 * Date: 10/16/2018
 */

namespace param\paramBasics;

class KS_Kart_Ekle
{
    public $KK_Sahibi;//Credit Card Holder
    public $KK_No;//Credit Card Number
    public $KK_SK_Ay;//Last 2 digit Expiration month
    public $KK_SK_Yil;//4 digit Expiration Year
    public $KK_CVC;//CVC Code
    public $GUID;//Key Belonging to Member Workplace
    public $G;//control and security object

    /**
     * KS_Kart_Ekle constructor.
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     * @param $Kart_No: Card Number Belonging to Member Workplace
     * @param $KK_Sahibi: Credit Card Holder
     * @param $KK_No: Credit Card Number
     * @param $KK_SK_Ay: Last 2 digit Expiration month
     * @param $KK_SK_Yil: 4 digit Expiration Year
     * @param $KK_CVC: CVC Code
     * @param string $Data1: Extra Space 1, Optional
     * @param string $Data2: Extra Space 2, Optional
     * @param string $Data3: Extra Space 3, Optional
     */
    public function __construct($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $guid,  $KK_Sahibi, $KK_No, $KK_SK_Ay, $KK_SK_Yil, $KK_CVC)
    {
        $this->KK_Sahibi = $KK_Sahibi;
        $this->KK_No = $KK_No;
        $this->KK_SK_Ay = $KK_SK_Ay;
        $this->KK_SK_Yil = $KK_SK_Yil;
        $this->KK_CVC = $KK_CVC;
        $this->GUID = $guid;
        $this->G = new G($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD);
    }
}