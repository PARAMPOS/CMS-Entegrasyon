<?php
/**
 * Created by PhpStorm.
 * User: mohmm
 * Date: 10/17/2018
 * Time: 5:16 PM
 */

namespace param;


use param\paramBasics\TP_Islem_Odeme_WKS;
use param\paramBasics\SHA2B64;

class SaleWithSavedCard extends Config
{
    /**
     * Sale constructor.
     * @param $clientCode: Terminal ID, It will be forwarded by param.
     * @param $clientUsername: User Name, It will be forwarded by param.
     * @param $clientPassword: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     * @param $mode: string value TEST/PROD
     */
    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode)
    {
        parent::__construct($clientCode, $clientUsername, $clientPassword, $guid, $mode);
    }

    /**
     * send sale transaction
     * @param $vPosId: is the VirtualPOS_ID value of the Card Brand selected from the customer method.
     * @param $receiverCardNumber: Card Number Belonging to Member Workplace
     * @param $savedCardGuid: GUID value that returns from KK_Saklama method
     * @param $cardHolderPhone: Credit Card holder GSM No, Without zero at the beginning (5xxxxxxxxx)
     * @param $failUrl: If the payment fails, page address to be redirected to
     * @param $successURL: If the payment is successful, page address to be redirected to
     * @param $orderId: Singular ID for Order-specific. If you have sent before this value the system is new Assign order_ID. As a result of this The order_ID is returned.
     * @param $orderDescription: Order Description
     * @param $installments: Selected number of installments. Send 1 for one installment.
     * @param $total: Order Amount, (only a comma with Kuruş format 1000,50)
     * @param $generalTotal: Commission Including Order Amount, (only a comma with Kuruş format 1000,50)
     * @param $transactionId: Single ID except the Sipariş Id that belongs to transaction, optional.
     * @param $ipAddress: IP Address
     * @param $referenceUrl: Url of page where payment is made
     * @param $extraData1: Extra Space 1
     * @param $extraData2: Extra Space 2
     * @param $extraData3: Extra Space 3
     * @param $extraData4: Extra Space 4
     * @param $cvc: CVC Code
     * @param $use3d: use 3d secure 1/0
     */
    public function send($vPosId,$receiverCardNumber,$savedCardGuid,
                         $cardHolderPhone,$failUrl,$successURL,$orderId,
                         $orderDescription,$installments,$total,$generalTotal,$transactionId,$ipAddress,
                         $referenceUrl,$extraData1,$extraData2,$extraData3,$extraData4,$cvc,$use3d)
    {
        $client = new \SoapClient($this->serviceUrl);
        $use3d = ($use3d == True)?'NS':'';

        $saleObj = new TP_Islem_Odeme_WKS($this->clientCode,$this->clientUsername,$this->clientPassword,$vPosId,$this->guid,$receiverCardNumber,
            $savedCardGuid,$cardHolderPhone,$failUrl,$successURL,$orderId,
            $orderDescription,$installments,$total,$generalTotal,$transactionId,$ipAddress,
            $referenceUrl,$extraData1,$extraData2,$extraData3,$extraData4,$cvc,$use3d);

        $securityString = $this->clientCode.$this->guid.$vPosId.$installments.$total.$generalTotal.$orderId.$failUrl.$successURL;
        $shaString = new SHA2B64($securityString, $this->clientCode,$this->clientUsername,$this->clientPassword);
        $saleObj->Islem_Hash = $client->SHA2B64($shaString)->SHA2B64Result;
        $response = $client->TP_Islem_Odeme_WKS($saleObj);
    }
}