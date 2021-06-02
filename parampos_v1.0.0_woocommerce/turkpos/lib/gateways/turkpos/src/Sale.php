<?php
/**
 * Created by Payfull.
 * Date: 10/29/2018
 */

namespace param;

use param\paramBasics\TP_Islem_Odeme_WNS;
use param\paramBasics\SHA2B64;

class Sale extends Sale3d
{
    /**
     * Sale constructor.
     * @param $clientCode: Terminal ID, It will be forwarded by param.
     * @param $clientUsername: User Name, It will be forwarded by param.
     * @param $clientPassword: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     * @param $mode: string value TEST/PROD
     * @param $newAPI: true/false for API endpoints
     */
    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $newAPI = false)
    {
        parent::__construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $newAPI);
    }

    /**
     * send sale transaction
     * @param $vPosId: is the VirtualPOS_ID value of the Card Brand selected from the customer method.
     * @param $cardHolder: Credit Card Holder
     * @param $cardNumber: Credit Card Number
     * @param $cardExpMonth: Last 2 digit Expiration month
     * @param $cardExpYear: 4 digit Expiration Year
     * @param $cvc: CVC Code
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
     * @param $extraData5: Extra Space 5
     */
    public function send($vPosId,$cardHolder,$cardNumber,
                         $cardExpMonth,$cardExpYear,$cvc,$cardHolderPhone,$failUrl,$successURL,$orderId,
                         $orderDescription,$installments,$total,$generalTotal,$transactionId,$ipAddress,
                         $referenceUrl,$extraData1,$extraData2,$extraData3,$extraData4,$extraData5)
    {
        $this->transactionId = $transactionId;
        $options = [
            'cache_wsdl'     => WSDL_CACHE_NONE,
            'trace'          => 1,
            'stream_context' => stream_context_create(
                [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true
                    ]
                ]
            )
        ];
        $client = new \SoapClient($this->serviceUrl,$options);

        $saleObj = new TP_Islem_Odeme_WNS($this->clientCode,$this->clientUsername,$this->clientPassword,$vPosId,$this->guid,
            $cardHolder,$cardNumber,$cardExpMonth,$cardExpYear,$cvc,$cardHolderPhone,$failUrl,$successURL,$orderId,
            $orderDescription,$installments,$total,$generalTotal,$transactionId,$ipAddress,
            $referenceUrl,$extraData1,$extraData2,$extraData3,$extraData4,$extraData5);


        $securityString = $this->clientCode.$this->guid.$vPosId.$installments.$total.$generalTotal.$orderId.$failUrl.$successURL;
        $shaString = new SHA2B64($securityString, $this->clientCode, $this->clientUsername, $this->clientPassword);
        $saleObj->Islem_Hash = $client->SHA2B64($shaString)->SHA2B64Result;
        $this->response = $client->TP_Islem_Odeme_WNS($saleObj);
    }

    /**
     * @return array|bool result array or false on not bad response format
     */
    public function parse()
    {
        if(isset($this->response->TP_Islem_Odeme_WNSResult)){
            $this->response->TP_Islem_OdemeResult = $this->response->TP_Islem_Odeme_WNSResult;
        }
        return parent::parse();
    }

}