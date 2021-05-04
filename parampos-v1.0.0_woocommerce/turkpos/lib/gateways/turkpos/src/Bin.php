<?php
/**
 * Created by Payfull.
 * Date: 10/18/2018
 */

namespace param;

use param\paramBasics\BIN_SanalPos;

class Bin extends Config
{
    private $response;//request response
    private $bin;//Card BIN Code

    /**
     * Bin constructor.
     * @param $clientCode: Terminal ID, It will be forwarded by param.
     * @param $clientUsername: User Name, It will be forwarded by param.
     * @param $clientPassword: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     * @param $mode: string value TEST/PROD
     */
    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl)
    {
        parent::__construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl);
    }

    /**
     * @param string $bin: Card BIN Code
     */
    public function send($bin = '')
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
        $client = new \SoapClient($this->serviceUrl,$options);
        $binObj = new BIN_SanalPos($this->clientCode,$this->clientUsername,$this->clientPassword,$this->bin);
        $this->response = $client->BIN_SanalPos($binObj);
    }

    /**
     * @return array result array
     */
    public function parse()
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