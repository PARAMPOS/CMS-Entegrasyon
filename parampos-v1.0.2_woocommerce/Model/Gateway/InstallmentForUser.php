<?php

class InstallmentForUser extends Client
{
    private $response;

    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl)
    {
        parent::__construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl);
    }

    /**
     * 
     *
     * @return void
     */
    public function send()
    {
        $options = [
            'soap_version' => 'SOAP_1_1',
            'cache_wsdl'     => WSDL_CACHE_NONE,
            'trace'          => 1,
            'stream_context' => stream_context_create(
                [
                    'ssl' => [
                        'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true
                    ]
                ]
            )
        ];
        $client = new \SoapClient($this->serviceUrl,$options);
        
        $installmentsObj = new \stdClass();
        $installmentsObj->G = new \stdClass();
        $installmentsObj->G->client_code  = $this->clientCode;
        $installmentsObj->G->client_username = $this->clientUsername;
        $installmentsObj->G->client_password = $this->clientPassword;
        $installmentsObj->GUID = $this->guid;
        
        $this->response = $client->TP_Ozel_Oran_SK_Liste($installmentsObj);
        
        return $this;
    }

    /**
     *
     * @return void
     */
    public function fetchInstallment()
    {
        $results = [];
        if($this->response->TP_Ozel_Oran_SK_ListeResult < 0){
            return [
                'Sonuc' => $this->response->TP_Ozel_Oran_SK_ListeResult->Sonuc,
                'Sonuc_Str' => $this->response->TP_Ozel_Oran_SK_ListeResult->Sonuc_Str,
            ];
        }

        $result = $this->response->TP_Ozel_Oran_SK_ListeResult;
        $sonuc = $result->{'Sonuc'};
        $sonucStr = $result->{'Sonuc_Str'};
        if($sonuc <= 0){
            return [
                'Sonuc' => $sonuc,
                'Sonuc_Str' => $sonucStr,
            ];
        }
        $DT_Bilgi = $result->{'DT_Bilgi'};
        $xml = $DT_Bilgi->{'any'};
        $xmlStr = '<?xml version=\'1.0\' standalone=\'yes\'?><root>'.$xml.'</root>';
        $xmlStr = str_replace(array("diffgr:","msdata:"),'', $xmlStr);
        $data = @simplexml_load_string($xmlStr);
        $list = $data->diffgram->NewDataSet;
        $installmentsArr = [];
        foreach ($list->DT_Ozel_Oranlar_SK as $instData){
            $installmentsArr[strtoupper($instData->Kredi_Karti_Banka)] = [(array)$instData];
        }
        return $installmentsArr;
    }

}