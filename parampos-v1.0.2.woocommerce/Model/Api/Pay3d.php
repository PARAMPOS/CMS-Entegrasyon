<?php

class Pay3d extends Client
{
    protected $response;
    protected $transactionId;

    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl)
    {
        parent::__construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl);
    }

    public function send($vPosId,$cardHolder,$cardNumber,
       $cardExpMonth,$cardExpYear,$cvc,$cardHolderPhone,$failUrl,$successURL,$orderId,
       $orderDescription,$installments,$total,$grandTotal,$transactionId,$ipAddress,
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
        $client = new \SoapClient($this->serviceUrl, $options);

        $saleObj = new Pos_Odeme($this->clientCode,$this->clientUsername,$this->clientPassword,$vPosId,$this->guid,
            $cardHolder,$cardNumber,$cardExpMonth,$cardExpYear,$cvc,$cardHolderPhone,$failUrl,$successURL,$orderId,
            $orderDescription,$installments,$total,$grandTotal,$transactionId,$ipAddress,
            $referenceUrl,$extraData1,$extraData2,$extraData3,$extraData4,$extraData5);
            
        $securityString = $this->clientCode.$this->guid.$installments.$total.$grandTotal.$orderId.$failUrl.$successURL;
        $sha2B64 = new \stdClass();
        $sha2B64->Data = $securityString;
        $sha2B64->G = new \stdClass();
        $sha2B64->G->CLIENT_CODE  = $this->clientCode;
        $sha2B64->G->CLIENT_USERNAME = $this->clientUsername;
        $sha2B64->G->CLIENT_PASSWORD = $this->clientPassword;
        
        $saleObj->Islem_Hash = $client->SHA2B64($sha2B64)->SHA2B64Result;
        $this->response = $client->Pos_Odeme($saleObj);
    }

    /**
     * @return array result array
     */
    public function parse()
    {
        if(is_object($this->response) == False OR !isset($this->response->Pos_OdemeResult->Sonuc))
        {
            return [
                'Sonuc' => -2,
                'Sonuc_Str' => 'Param response has wrong format',
            ];
        }
        else
        {
            return (array)$this->response->Pos_OdemeResult;
        }
    }
    
}