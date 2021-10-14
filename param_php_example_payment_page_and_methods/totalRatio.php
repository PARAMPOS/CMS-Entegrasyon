<?php
include_once('env.php');
include 'GeneralClass.php';
include "TotalSpecialRatio.php";
class TP_Ozel_Oran_Liste
{

    function __construct($guid)
    {
        global $env;

        $this->GUID = $guid;
        $this->G = new GeneralClass($env['CLIENT_CODE'], $env['CLIENT_USERNAME'], $env['CLIENT_PASSWORD']);

    }
}
$gguid = $env['GUID'];
$Bagla = new SoapClient($env['URL']);
$OzelOranListe = new TP_Ozel_Oran_Liste($gguid);
$Veri =  $Bagla->TP_Ozel_Oran_Liste($OzelOranListe);
$q1 = $Veri->TP_Ozel_Oran_ListeResult;
$DT_Bilgi = $q1->{'DT_Bilgi'};
$Sonuc = $q1->{'Sonuc'};
$Sonuc_Str = $q1->{'Sonuc_Str'};
$xml = $DT_Bilgi->{'any'};
$xmlstr = <<<XML
<?xml version='1.0' standalone='yes'?>
<root>
{$xml}
</root>
XML;
$xmlstr = str_replace(array("diffgr:", "msdata:"), '', $xmlstr);
$data = simplexml_load_string($xmlstr);
$oranListesiT = $data->diffgram->NewDataSet;
$OzelOranSKListe = new TotalSpecialRatio($gguid);
$Veri2 = $Bagla->TP_Ozel_Oran_SK_Liste($OzelOranSKListe);
$q2 = $Veri2->TP_Ozel_Oran_SK_ListeResult;
$DT_Bilgi2 = $q2->{'DT_Bilgi'};
$Sonuc1 = $q2->{'Sonuc'};
$Sonuc_Str1 = $q2->{'Sonuc_Str'};
$xml1 = $DT_Bilgi2->{'any'};
$xmlstr1 = <<<XML
<?xml version='1.0' standalone='yes'?>
<root>
{$xml1}
</root>
XML;
$xmlstr1 = str_replace(array("diffgr:", "msdata:"), '', $xmlstr1);
$data1 = simplexml_load_string($xmlstr1);
$oranListesi = $data1->diffgram->NewDataSet;
?>
