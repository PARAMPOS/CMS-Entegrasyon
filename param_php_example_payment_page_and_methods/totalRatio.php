<?php
include_once('env.php');
include 'GeneralClass.php';
include "TotalSpecialRatio.php";
class TP_Ozel_Oran_Liste
{


    function __construct($globallyUniqueIdentifier)


    {


        global $env;


        $this->GUID = $globallyUniqueIdentifier;


        $this->G = new GeneralClass($env['CLIENT_CODE'], $env['CLIENT_USERNAME'], $env['CLIENT_PASSWORD']);


    }


}
$globallyUniqueIdentifier = $env['GUID'];


$connect = new SoapClient($env['URL']);


$specialRatioList = new TP_Ozel_Oran_Liste($globallyUniqueIdentifier);


$variable =  $connect->TP_Ozel_Oran_Liste($specialRatioList);


$result = $variable->TP_Ozel_Oran_ListeResult;


$dtInfo = $result->{'DT_Bilgi'};


$finalResult = $result->{'Sonuc'};


$finalResultString = $result->{'Sonuc_Str'};


$xml = $dtInfo->{'any'};


$xmlString = <<<XML
<?xml version='1.0' standalone='yes'?>
<root>
{$xml}
</root>
XML;


$xmlString = str_replace(array("diffgr:", "msdata:"), '', $xmlString);


$data = simplexml_load_string($xmlString);


$ratioList = $data->diffgram->NewDataSet;


$specialRatioSKList = new TotalSpecialRatio($globallyUniqueIdentifier);


$variableTwo = $connect->TP_Ozel_Oran_SK_Liste($specialRatioSKList);


$resultTwo = $variableTwo->TP_Ozel_Oran_SK_ListeResult;


$DTInfoTwo = $resultTwo->{'DT_Bilgi'};


$finalResultTwo = $resultTwo->{'Sonuc'};


$finalResultStringTwo = $resultTwo->{'Sonuc_Str'};






$xmlOne = $DTInfoTwo->{'any'};

$xmlStringOne = <<<XML
<?xml version='1.0' standalone='yes'?>
<root>
{$xmlOne}
</root>
XML;

$xmlStringOne = str_replace(array("diffgr:", "msdata:"), '', $xmlStringOne);


$loadXml = simplexml_load_string($xmlStringOne);


$ratioListXml = $loadXml->diffgram->NewDataSet;






?>
