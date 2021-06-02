<?php
/**
 * Created by Payfull.
 * Date: 11/2/2018
 */

namespace param\paramBasics;

class KK_Kart_Sil
{
    /**
     * TP_Islem_Odeme constructor.
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     * @param $KS_GUID: Name of Credit Card to be stored, Optional.
     * @param $KK_Islem_ID : The singular ID of the Credit Card to be stored by you, Optional
     */
    public function __construct($CLIENT_CODE,$CLIENT_USERNAME,$CLIENT_PASSWORD,$KS_GUID,$KK_Islem_ID)
    {
        $this->KS_GUID = $KS_GUID;
        $this->KK_Islem_ID = $KK_Islem_ID;
        $this->G = new G($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD);
    }
}