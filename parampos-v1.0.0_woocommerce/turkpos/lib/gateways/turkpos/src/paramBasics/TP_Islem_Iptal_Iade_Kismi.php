<?php
/**
 * Created by Payfull.
 * Date: 10/30/2018
 */

namespace param\paramBasics;

class TP_Islem_Iptal_Iade_Kismi
{
    public $G;//control and security object
    public $GUID;//Key Belonging to Member Workplace
    public $Durum;//For cancellation IPTAL For return IADE
    public $Dekont_ID;//Transaction’s receipt ID.
    public $Tutar;//Cancellation / Return Amount, All amount must be written for CANCELLATION. All amount or smaller amount (partial) must be written for RETURN.

    /**
     * TP_Islem_Iptal_Iade_Kismi constructor.
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     * @param $type: For cancellation IPTAL For return IADE
     * @param $invoiceId: Transaction’s receipt ID.
     * @param $totalAmount: Cancellation / Return Amount, All amount must be written for CANCELLATION. All amount or smaller amount (partial) must be written for RETURN.
     */
    public function __construct($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $guid, $type, $invoiceId, $totalAmount)
    {
        $this->GUID = $guid;
        $this->Durum = $type;
        $this->Dekont_ID = $invoiceId;
        $this->Tutar = $totalAmount;
        $this->G = new G($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD);
    }
}