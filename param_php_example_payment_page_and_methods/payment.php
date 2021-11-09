<?php
ob_start();
session_start();
@include('validation.php');
@include('env.php');
@include "Auth.php";
@include "TotalPaymentTransaction.php";
@include "GeneralClass.php";
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
function isSucceced($value)
{
    if ($value->TP_Islem_OdemeResult->Sonuc > 0) {
        echo "<script>window.top.location='".$value->TP_Islem_OdemeResult->UCD_URL."'</script>";
    } else {
        ?>
        <script>
            alert("Hata meydana geldi.")
        </script>
        <?php

    }
}


if ($_POST) {
    $client = new SoapClient($env['URL']);
    $transactionsValueList = [
        "cardType" => $_POST['odemetip'],
        "spid" => $_POST['odemetip'],
        "guid" => $env['GUID'],
        "cardHolderName" => $_POST['cardName'],
        "cardNo" => $_POST['cardNumber'],
        "monthOfExpireDate" => $_POST['expMonth'],
        "yearOfExpireDate" => "20" . $_POST['expYear'],
        "creditCardCvc" => $_POST['cvCode'],
        "creditCardOwnerName" => "5372403939",
        "errorUrl" => "http://localhost/param_php_example_payment_page_and_methods/result.php?status=0",
        "succesUrl" => "http://localhost/param_php_example_payment_page_and_methods/result.php?status=1",
        "orderID" => rand(0,999999),
        "paymentUrl" => "http://localhost/param_php_example_payment_page_and_methods/index.php",
        "orderExplanation" => date("d-m-Y H:i:s") . " tarihindeki ödeme",
        "installment" => $_POST['odemetaksit'],
        "transactionPayment" => $_POST['odemetutar'],
        "totalPayment" => $_POST['odemetutar'],
        "transactionID" => "",
        "ipAdr" => "192.168.168.115"
    ];


    $data = new TotalPaymentTransaction(
        $transactionsValueList["cardType"],
        "",
        $transactionsValueList["guid"],
        $transactionsValueList["cardHolderName"],
        $transactionsValueList["cardNo"],
        $transactionsValueList["monthOfExpireDate"],
        $transactionsValueList["yearOfExpireDate"],
        $transactionsValueList["creditCardCvc"],
        $transactionsValueList["creditCardOwnerName"],
        $transactionsValueList["errorUrl"],
        $transactionsValueList["succesUrl"],
        $transactionsValueList["orderID"],
        $transactionsValueList["orderExplanation"],
        $transactionsValueList["installment"],
        $transactionsValueList["transactionPayment"],
        $transactionsValueList["totalPayment"],
        $transactionsValueList["transactionID"],
        $transactionsValueList["ipAdr"],
        $transactionsValueList["paymentUrl"]
    );


    $authObject = new Auth($transactionSecurityStr = $env['CLIENT_CODE'].
        $transactionsValueList["guid"].
        $transactionsValueList["spid"].
        $transactionsValueList["installment"].
        $transactionsValueList["transactionPayment"].
        $transactionsValueList["totalPayment"].
        $transactionsValueList["orderID"].
        $transactionsValueList["errorUrl"].
        $transactionsValueList["succesUrl"]);

    $data->Islem_Hash = $client->SHA2B64($authObject)->SHA2B64Result;
    print_r($data);
    $response = $client->TP_Islem_Odeme($data);
    print_r($response);
    isSucceced($response);


}


?>