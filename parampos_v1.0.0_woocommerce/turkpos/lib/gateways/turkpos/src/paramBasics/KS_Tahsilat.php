<?php 
namespace param\paramBasics;
class KS_Tahsilat{
    function __construct(
        $CLIENT_CODE,
        $CLIENT_USERNAME,
        $CLIENT_PASSWORD,
        $guid,
        $SanalPOS_ID,
        $kkSahibi,
        $KK_GUID,
        $kkCvc,
        $kkSahibiGsm,
        $hataUrl,
        $basariliUrl,
        $siparisId,
        $siparisAciklama,
        $taksit,
        $islemtutar,
        $toplamTutar,
        $islemid,
        $ipAdr,
        $RefUrl,
        $use3d
    )
    {
        $this->G = new G($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD);
        $this->SanalPOS_ID = $SanalPOS_ID;
        $this->GUID = $guid;
        $this->KS_GUID  = $KK_GUID;
        $this->CVV = $kkCvc;
        $this->KK_Sahibi_GSM = $kkSahibiGsm;
        $this->Hata_URL = $hataUrl;
        $this->Basarili_URL = $basariliUrl;
        $this->Siparis_ID = $siparisId;
        $this->Siparis_Aciklama = $siparisAciklama;
        $this->Taksit= $taksit;
        $this->Islem_Tutar= $islemtutar;
        $this->Toplam_Tutar  = $toplamTutar;
        $this->Islem_Guvenlik_Tip = $use3d;
        $this->Islem_ID = $islemid;
        $this->IPAdr = $ipAdr;
        $this->Ref_URL = $RefUrl;
        $this->KK_Islem_ID  = "P_F_".rand(10000, 1000000);
    }
}