<?php
class TotalPaymentTransaction
{
    function __construct(
        $sPosId,
        $doviz,
        $guid,
        $kkSahibi,
        $kkNo,
        $kkSkAy,
        $kkSkYil,
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
        $dataBir,
        $dataIki,
        $dataUc,
        $dataDort,
        $dataBes
    ) {
        global $env;

        $this->SanalPOS_ID      = $sPosId;
        $this->Doviz             = $doviz;
        $this->GUID             = $guid;
        $this->KK_Sahibi        = $kkSahibi;
        $this->KK_No            = $kkNo;
        $this->KK_SK_Ay         = $kkSkAy;
        $this->KK_SK_Yil        = $kkSkYil;
        $this->KK_CVC           = $kkCvc;
        $this->KK_Sahibi_GSM    = $kkSahibiGsm;
        $this->Hata_URL         = $hataUrl;
        $this->Basarili_URL     = $basariliUrl;
        $this->Siparis_ID       = $siparisId;
        $this->Siparis_Aciklama = $siparisAciklama;
        $this->Taksit           = $taksit;
        $this->Islem_Tutar      = $islemtutar;
        $this->Toplam_Tutar     = $toplamTutar;
        $this->Islem_Hash       = null;
        $this->Islem_ID         = $islemid;
        $this->IPAdr            = $ipAdr;
        $this->Ref_URL          = $RefUrl;
        $this->Data1            = $dataBir;
        $this->Data2            = $dataIki;
        $this->Data3            = $dataUc;
        $this->Data4            = $dataDort;
        $this->Data5            = $dataBes;

        $this->G = new GeneralClass($env['CLIENT_CODE'], $env['CLIENT_USERNAME'], $env['CLIENT_PASSWORD']);
    }
}