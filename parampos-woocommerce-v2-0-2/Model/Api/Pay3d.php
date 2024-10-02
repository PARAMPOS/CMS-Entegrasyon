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

        $currency = get_option('woocommerce_currency');
        global $WOOCS;
        $currency = get_option('woocommerce_currency');
        if($currency == 'USD' || $currency == 'EUR' || $WOOCS->current_currency == 'EUR' || $WOOCS->current_currency == 'USD')
        {
            $saleObj = new TP_Islem_Odeme_WD($this->clientCode,$this->clientUsername,$this->clientPassword,$vPosId,$this->guid,
            $cardHolder,$cardNumber,$cardExpMonth,$cardExpYear,$cvc,$cardHolderPhone,$failUrl,$successURL,$orderId,
            $orderDescription,$installments,$total,$grandTotal,$transactionId,$ipAddress,
            $referenceUrl,$extraData1,$extraData2,$extraData3,$extraData4,$extraData5);

            $securityString = $this->clientCode.$this->guid.$total.$grandTotal.$orderId.$failUrl.$successURL;
            $sha2B64 = new \stdClass();
            $sha2B64->Data = $securityString;
            $sha2B64->G = new \stdClass();
            $sha2B64->G->CLIENT_CODE  = $this->clientCode;
            $sha2B64->G->CLIENT_USERNAME = $this->clientUsername;
            $sha2B64->G->CLIENT_PASSWORD = $this->clientPassword;
            $saleObj->Islem_Hash = $client->SHA2B64($sha2B64)->SHA2B64Result;

            $this->response = $client->TP_Islem_Odeme_WD($saleObj);
        } else
        {
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
    }

    /**
     * @return array result array
     */
    public function parse()
    {
        $response = $this->getResponse($this->response);
        if(isset($response['Sonuc']) && $response['Sonuc'] < 0)
        {
            return [
                'Sonuc' => $response['Sonuc'],
                'Sonuc_Str' => $response['Sonuc_Str'],
            ];
        }
        else {
            return (array)$response;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $obj
     * @return void
     */
    public function getResponse($obj) {
        $response = [];
        $response['Sonuc'] = -99;
        $response['Sonuc_Str'] = "Hata!";

        if (is_object($obj) ) {
            foreach ($obj as $property => $value) {
                return $response = (array)$value;
            }
        }
        return  $response;
    }
}
