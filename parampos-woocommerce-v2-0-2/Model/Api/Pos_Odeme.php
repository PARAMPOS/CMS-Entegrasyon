<?php

class Pos_Odeme
{
    public $G;
    public $SanalPOS_ID;
    public $GUID;
    public $KK_Sahibi;
    public $KK_No;
    public $KK_SK_Ay;
    public $KK_SK_Yil;
    public $KK_CVC;
    public $KK_Sahibi_GSM;
    public $Hata_URL;
    public $Basarili_URL;
    public $Siparis_ID;
    public $Siparis_Aciklama;
    public $Taksit;
    public $Islem_Tutar;
    public $Toplam_Tutar;
    public $Islem_Hash;
    public $Islem_ID;
    public $IPAdr;
    public $Ref_URL;
    public $Data1;
    public $Data2;
    public $Data3;
    public $Data4;
    public $Data5;
    public $Islem_Guvenlik_Tip;

    public function __construct($CLIENT_CODE,$CLIENT_USERNAME,$CLIENT_PASSWORD,$sPosId,$guid,$ccSahibi,$ccNo,$ccSkAy,$ccSkYil,$ccCvc,$ccSahibiGsm,$hataUrl,$basariliUrl,$siparisId,$siparisAciklama,$taksit,$islemtutar,$toplamTutar,$islemId,$ipAdr,$RefUrl,$dataBir,$dataIki,$dataUc,$dataDort,$dataBes)
    {
        $this->G = new stdClass();
        $this->G->CLIENT_CODE  = $CLIENT_CODE;
        $this->G->CLIENT_USERNAME = $CLIENT_USERNAME;
        $this->G->CLIENT_PASSWORD = $CLIENT_PASSWORD;
        $this->GUID = $guid;
        $this->KK_Sahibi= $ccSahibi;
        $this->KK_No = $ccNo;
        $this->KK_SK_Ay = $ccSkAy;
        $this->KK_SK_Yil = $ccSkYil;
        $this->KK_CVC = $ccCvc;
        $this->KK_Sahibi_GSM = $ccSahibiGsm;
        $this->Hata_URL = $hataUrl;
        $this->Basarili_URL = $basariliUrl;
        $this->Siparis_ID = $siparisId;
        $this->Siparis_Aciklama = $siparisAciklama;
        $this->Taksit= $taksit;
        $this->Islem_Tutar= $islemtutar;
        $this->Toplam_Tutar  = $toplamTutar;
        $this->Islem_Guvenlik_Tip = '3D';
        $this->Islem_Hash = null;
        $this->Islem_ID = $islemId;
        $this->IPAdr = $ipAdr;
        $this->Ref_URL = $RefUrl;
        $this->Data1 = $dataBir;
        $this->Data2 = $dataIki;
        $this->Data3 = $dataUc;
        $this->Data4 = $dataDort;
        $this->Data5 = $dataBes;
    }
}
