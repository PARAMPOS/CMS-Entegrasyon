<?php

class Bin extends Client
{
    private $response;
    private $bin;

    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl)
    {
        parent::__construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl);
    }

    public function send($bin)
    {
        $this->bin = (strlen($bin) >= 6)?substr($bin, 0, 6):'';
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
        $bin = new stdClass();
        $bin->BIN = $this->bin;
        $bin->G = new stdClass();
        $bin->G->CLIENT_CODE  = $this->clientCode;
        $bin->G->CLIENT_USERNAME = $this->clientUsername;
        $bin->G->CLIENT_PASSWORD = $this->clientPassword;
        $this->response = $client->BIN_SanalPos($bin);

        return $this;
    }

    /**
     * @return array result array
     */
    public function fetchBIN()
    {
        $results = [];
        if(isset($this->response->BIN_SanalPosResult) == False){
            return [
                'Sonuc' => -2,
                'Sonuc_Str' => 'Param response has wrong format',
            ];
        }

        $q1 = $this->response->BIN_SanalPosResult;
        $DT_Bilgi = $q1->{'DT_Bilgi'};
        $Sonuc = $q1->{'Sonuc'};
        $Sonuc_Str = $q1->{'Sonuc_Str'};
        $xml = $DT_Bilgi->{'any'};
        $xmlStr = '<?xml version=\'1.0\' standalone=\'yes\'?><root>'.$xml.'</root>';
        $xmlStr = str_replace(array("diffgr:","msdata:"),'', $xmlStr);
        $data = @simplexml_load_string($xmlStr);
        $list = $data->diffgram->NewDataSet;
        foreach ($list->Temp as $card){
            $card = (array)$card;
            $results[] = [
                'bin' => $card['BIN'],
                'posId' => $card['SanalPOS_ID'],
                'posName' => $card['Kart_Banka'],
            ];
            if($this->bin != ''){
                return $results[0];
            }
        }
        return $results;
    }

}