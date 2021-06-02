<?php
/**
 * Created by Payfull.
 * Date: 11/2/2018
 */

namespace param;

use param\paramBasics\KK_Kart_Sil;

class DeleteCard extends Config
{
    protected $response;//request response

    /**
     * DeleteCard constructor.
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
     * @param $cardGuid: Name of Credit Card to be stored, Optional.
     * @param $transactionId: The singular ID of the Credit Card to be stored by you, Optional
     */
    public function send($cardGuid, $transactionId = '')
    {
        $client = new \SoapClient($this->serviceUrl);
        $deleteCardObj = new KK_Kart_Sil($this->clientCode,$this->clientUsername,$this->clientPassword,$cardGuid,$transactionId);
        $this->response = $client->KK_Kart_Sil($deleteCardObj);
    }

    /**
     * @return array result array
     */
    public function parse()
    {
        if(isset($this->response->KK_Kart_SilResult) == False){
            return [
                'Sonuc' => -2,
                'Sonuc_Str' => 'Param response has wrong format',
            ];
        }else{
            return (array)$this->response->KK_Kart_SilResult;
        }
    }
}