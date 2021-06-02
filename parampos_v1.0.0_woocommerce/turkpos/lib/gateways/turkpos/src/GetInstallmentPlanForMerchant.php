<?php
/**
 * Created by Payfull.
 * Date: 14/1/2019
 */

namespace param;

use param\paramBasics\TP_Ozel_Oran_Liste;

class GetInstallmentPlanForMerchant extends Config
{
    private $response;//request response

    /**
     * GetInstallmentPlanForMerchant constructor.
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
     * send request to get the installments plan list for Merchant
     * @return array|bool
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
        $installmentsListObj = new TP_Ozel_Oran_Liste($this->clientCode,$this->clientUsername,$this->clientPassword,$this->guid);
        $this->response = $client->TP_Ozel_Oran_Liste($installmentsListObj);
    }

    /**
     * @return array result array
     */
    public function parse()
    {
        $results = [];
        if(isset($this->response->TP_Ozel_Oran_ListeResult) == False){
            return [
                'Sonuc' => -2,
                'Sonuc_Str' => 'Param response has wrong format',
            ];
        }
        $q1 = $this->response->TP_Ozel_Oran_ListeResult;
        $Sonuc = $q1->{'Sonuc'};
        $Sonuc_Str = $q1->{'Sonuc_Str'};
        if($Sonuc <= 0){
            return [
                'Sonuc' => $Sonuc,
                'Sonuc_Str' => $Sonuc_Str,
                ];
        }
        $DT_Bilgi = $q1->{'DT_Bilgi'};
        $xml = $DT_Bilgi->{'any'};
        $xmlStr = '<?xml version=\'1.0\' standalone=\'yes\'?><root>'.$xml.'</root>';
        $xmlStr = str_replace(array("diffgr:","msdata:"),'', $xmlStr);
        $data = @simplexml_load_string($xmlStr);
        $list = $data->diffgram->NewDataSet;
        $installmentsArr = [];
        foreach ($list->DT_Ozel_Oranlar as $instData){
            $installmentsArr[strtoupper($instData->Kredi_Karti_Banka)] = [(array)$instData];
        }
        return $installmentsArr;
    }

}