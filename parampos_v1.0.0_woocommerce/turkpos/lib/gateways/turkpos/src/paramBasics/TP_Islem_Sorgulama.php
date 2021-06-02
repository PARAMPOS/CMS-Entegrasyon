<?php
/**
 * Created by Payfull.
 * Date: 10/15/2018
 */

namespace param\paramBasics;

class TP_Islem_Sorgulama
{
    public $GUID;//Key Belonging to Member Workplace
    public $Dekont_ID;//Dekont_ID which is POSTed after successful transaction, optional.
    public $Siparis_ID;//Posted Order ID after successful transaction.
    public $Islem_ID;//Transaction ID sent to TP_Islem_Odeme method, optional.
    public $G;//control and security object

    /**
     * TP_Islem_Sorgulama constructor.
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     * @param $dekont: Dekont_ID which is POSTed after successful transaction, optional.
     * @param $siparis: Posted Order ID after successful transaction.
     * @param $islem: Transaction ID sent to TP_Islem_Odeme method, optional.
     */
    public function __construct($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD, $guid, $dekont, $siparis, $islem)
    {
        $this->GUID = $guid;
        $this->Dekont_ID= $dekont;
        $this->Siparis_ID = $siparis;
        $this->Islem_ID = $islem;
        $this->G = new G($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD);
    }

}