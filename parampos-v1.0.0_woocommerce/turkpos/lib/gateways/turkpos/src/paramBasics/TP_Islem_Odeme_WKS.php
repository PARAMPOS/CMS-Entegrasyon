<?php
/**
 * Created by Payfull.
 * Date: 11/16/2018
 */

namespace param\paramBasics;

class TP_Islem_Odeme_WKS
{
    public $SanalPOS_ID;//is the VirtualPOS_ID value of the Card Brand selected from the customer method.
    public $GUID;//Key Belonging to Member Workplace
    public $KS_Kart_No;//Card Number Belonging to Member Workplace
    public $KK_GUID;//GUID value that returns from KK_Saklama method
    public $KK_Sahibi_GSM;//Credit Card holder GSM No, Without zero at the beginning (5xxxxxxxxx)
    public $Hata_URL;//If the payment fails, page address to be redirected to
    public $Basarili_URL;//If the payment is successful, page address to be redirected to
    public $Siparis_ID;//Singular ID for Order-specific. If you have sent before this value the system is new Assign order_ID. As a result of this The order_ID is returned.
    public $Siparis_Aciklama;//Order Description
    public $Taksit;//Selected number of installments. Send 1 for one installment.
    public $Islem_Tutar;//Order Amount, (only a comma with Kuruş format 1000,50)
    public $Toplam_Tutar;//Commission Including Order Amount, (only a comma with Kuruş format 1000,50)
    public $Islem_Hash;//Transaction Hash Value
    public $Islem_Guvenlik_Tip;//NS (NonSecure) or 3D will sent
    public $Islem_ID;//Single ID except the Sipariş Id that belongs to transaction, optional.
    public $IPAdr;//IP Address
    public $Ref_URL;//Url of page where payment is made
    public $Data5;//CVC code
    public $G;//control and security object

    /**
     * TP_Islem_Odeme constructor.
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     * @param $sPosId: is the VirtualPOS_ID value of the Card Brand selected from the customer method.
     * @param $guid: Key Belonging to Member Workplace
     * @param $KS_Kart_No: Card Number Belonging to Member Workplace
     * @param $KK_GUID: GUID value that returns from KK_Saklama method
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
     * @param $kkCvc: cvc code
     * @param $Islem_Guvenlik_Tip: NS (NonSecure) or 3D will sent
     */
    public function __construct($CLIENT_CODE,$CLIENT_USERNAME,$CLIENT_PASSWORD,$sPosId,$guid,$KS_Kart_No,$KK_GUID,$kkSahibiGsm,$hataUrl,$basariliUrl,$siparisId,$siparisAciklama,$taksit,$islemtutar,$toplamTutar,$islemid,$ipAdr,$RefUrl,$kkCvc,$Islem_Guvenlik_Tip)
    {
        $this->SanalPOS_ID = $sPosId;
        $this->GUID = $guid;
        $this->KS_Kart_No= $KS_Kart_No;
        $this->KK_GUID = $KK_GUID;
        $this->KK_Sahibi_GSM = $kkSahibiGsm;
        $this->Hata_URL = $hataUrl;
        $this->Basarili_URL = $basariliUrl;
        $this->Siparis_ID = $siparisId;
        $this->Siparis_Aciklama = $siparisAciklama;
        $this->Taksit= $taksit;
        $this->Islem_Tutar= $islemtutar;
        $this->Toplam_Tutar  = $toplamTutar;
        $this->Islem_Hash = null;
        $this->Islem_ID = $islemid;
        $this->IPAdr = $ipAdr;
        $this->Ref_URL = $RefUrl;
        $this->Islem_Guvenlik_Tip = $Islem_Guvenlik_Tip;
        $this->G = new G($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD);
    }
}