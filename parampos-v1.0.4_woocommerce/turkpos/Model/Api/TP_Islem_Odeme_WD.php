<?php

class TP_Islem_Odeme_WD extends Pos_Odeme
{
    public $Islem_Guvenlik_Tip;

    public function __construct($CLIENT_CODE,$CLIENT_USERNAME,$CLIENT_PASSWORD,$sPosId,$guid,$kkSahibi,$kkNo,$kkSkAy,$kkSkYil,$kkCvc,$kkSahibiGsm,$hataUrl,$basariliUrl,$siparisId,$siparisAciklama,$taksit,$islemtutar,$toplamTutar,$islemid,$ipAdr,$RefUrl,$dataBir,$dataIki,$dataUc,$dataDort,$dataBes)
    {
        parent:: __construct($CLIENT_CODE,$CLIENT_USERNAME,$CLIENT_PASSWORD,$sPosId,$guid,$kkSahibi,$kkNo,$kkSkAy,$kkSkYil,$kkCvc,$kkSahibiGsm,$hataUrl,$basariliUrl,$siparisId,$siparisAciklama,$taksit,$islemtutar,$toplamTutar,$islemid,$ipAdr,$RefUrl,$dataBir,$dataIki,$dataUc,$dataDort,$dataBes);
        
        $this->Islem_Guvenlik_Tip = '3D';

        $currency = get_option('woocommerce_currency');
        switch ($currency) {
            case 'EUR':
                $this->Doviz_Kodu = 1002;
                break;
            case 'USD':
                $this->Doviz_Kodu = 1001;
                break;
            default:
                $this->Doviz_Kodu = 1000;
                break;
        }
    }
}