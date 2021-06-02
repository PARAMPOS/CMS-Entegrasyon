<?php
/**
 * Created by Payfull.
 * Date: 10/29/2018
 */

namespace param\paramBasics;

class TP_Islem_Odeme_WNS extends TP_Islem_Odeme
{
    public $Islem_Guvenlik_Tip;//set it to NS to have no 3d secure transaction

    /**
     * TP_Islem_Odeme_WNS constructor.
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     * @param $sPosId: is the VirtualPOS_ID value of the Card Brand selected from the customer method.
     * @param $guid: Key Belonging to Member Workplace
     * @param $kkSahibi: Credit Card Holder
     * @param $kkNo: Credit Card Number
     * @param $kkSkAy: Last 2 digit Expiration month
     * @param $kkSkYil: 4 digit Expiration Year
     * @param $kkCvc: CVC Code
     * @param $kkSahibiGsm: Credit Card holder GSM No, Without zero at the beginning (5xxxxxxxxx)
     * @param $hataUrl: If the payment fails, page address to be redirected to
     * @param $basariliUrl: If the payment is successful, page address to be redirected to
     * @param $siparisId: Singular ID for Order-specific. If you have sent before this value the system is new Assign order_ID. As a result of this The order_ID is returned.
     * @param $siparisAciklama: Order Description
     * @param $taksit: Selected number of installments. Send 1 for one installment.
     * @param $islemtutar: Order Amount, (only a comma with Kuruş format 1000,50)
     * @param $toplamTutar: Commission Including Order Amount, (only a comma with Kuruş format 1000,50)
     * @param $islemid: Single ID except the Sipariş Id that belongs to transaction, optional.
     * @param $ipAdr: IP Address
     * @param $RefUrl: Url of page where payment is made
     * @param $dataBir: Extra Space 1
     * @param $dataIki: Extra Space 2
     * @param $dataUc: Extra Space 3
     * @param $dataDort: Extra Space 4
     * @param $dataBes: Extra Space 5
     */
    public function __construct($CLIENT_CODE,$CLIENT_USERNAME,$CLIENT_PASSWORD,$sPosId,$guid,$kkSahibi,$kkNo,$kkSkAy,$kkSkYil,$kkCvc,$kkSahibiGsm,$hataUrl,$basariliUrl,$siparisId,$siparisAciklama,$taksit,$islemtutar,$toplamTutar,$islemid,$ipAdr,$RefUrl,$dataBir,$dataIki,$dataUc,$dataDort,$dataBes)
    {
        parent:: __construct($CLIENT_CODE,$CLIENT_USERNAME,$CLIENT_PASSWORD,$sPosId,$guid,$kkSahibi,$kkNo,$kkSkAy,$kkSkYil,$kkCvc,$kkSahibiGsm,$hataUrl,$basariliUrl,$siparisId,$siparisAciklama,$taksit,$islemtutar,$toplamTutar,$islemid,$ipAdr,$RefUrl,$dataBir,$dataIki,$dataUc,$dataDort,$dataBes);
        $this->Islem_Guvenlik_Tip = 'NS';
    }
}