<?php
session_start();
include('redirect.php');
include('env.php');
include "Auth.php";
include "TotalPaymentTransaction.php";
include "GeneralClass.php";
if ($_POST) {
    $client = new SoapClient($env['URL']);
    $cardType = $_POST['odemetip'];
    $spid = $cardType;
    $guid = $env['GUID'];
    $cardHolderName = $_POST['cardName'];
    $cardNo = $_POST['cardNumber'];
    $monthOfExpireDate = $_POST['expMonth'];
    $yearOfExpireDate = "20" . $_POST['expYear'];
    $creditCardCvc = $_POST['cvCode'];
    $creditCardOwnerName = "5372403939";
    //HATALI ÖDEME YÖNLENDİRME
    $errorUrl = "https://localhost/param_php_example_payment_page_and_methods/result.php?status=0&refer=" . $refer;
    //BAŞARILI ÖDEME YÖNLENDİRME
    $succesUrl = "https://localhost/param_php_example_payment_page_and_methods/result.php?status=1&refer=" . $refer;
    //SİPARİŞ İD
    $orderID = "1123123";
    $paymentUrl = "https://localhost/param_php_example_payment_page_and_methods/index.php?refer=" . $refer;
    $orderExplanation = date("d-m-Y H:i:s") . " tarihindeki ödeme";
    $installment = $_POST['odemetaksit'];
    $transactionPayment = $_POST['odemetutar'];
    $totalPayment = $_POST['odemetutar'];
    $transactionID = "";
    $ipAdr = "192.168.168.115";
    $dataBir = " ";
    $dataIki = " ";
    $dataUc = " ";
    $dataDort = " ";
    $dataBes = " ";
    $currency = "";
    //$nesne              = new TP_Islem_Odeme($spid, $guid, $kkSahibi, $kkNo, $kkSkAy, $kkSkYil, $kkCvc, $kkSahibiGsm, $hataUrl, $basariliUrl, $siparisId, $siparisAciklama, $taksit, $islemtutar, $toplamTutar, $islemid, $ipAdr, $odemeUrl, $dataBir, $dataIki, $dataUc, $dataDort, $dataBes);
    $object = new TotalPaymentTransaction($cardType, $currency, $guid, $cardHolderName, $cardNo, $monthOfExpireDate, $yearOfExpireDate, $creditCardCvc, $creditCardOwnerName, $errorUrl, $succesUrl, $orderID, $orderExplanation, $installment, $transactionPayment, $totalPayment, $transactionID, $ipAdr, $paymentUrl, $dataBir, $dataIki, $dataUc, $dataDort, $dataBes);
    $transactionSecurityStr = $env['CLIENT_CODE'] . $guid . $spid . $installment . $transactionPayment . $totalPayment . $orderID . $errorUrl . $succesUrl;
    $nesneSha = new Auth($transactionSecurityStr);
    $Islem_Hash = $client->SHA2B64($nesneSha);
    $object->Islem_Hash = $client->SHA2B64($nesneSha)->SHA2B64Result;
    $response = $client->TP_Islem_Odeme($object);
    //Islem_ID
    if ($response->TP_Islem_OdemeResult->Sonuc > 0) {
        var_dump($response);
        header("location: " . $response->TP_Islem_OdemeResult->UCD_URL);
    } else {
        echo "<pre>";
        print_r($response);
        echo "</pre>";
        exit();
    }
}

?>