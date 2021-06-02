<?php
/**
 * Created by PhpStorm.
 * User: mohmm
 * Date: 10/16/2018
 * Time: 5:15 PM
 */

namespace param;

use param\paramBasics\TP_Ozel_Oran_SK_Guncelle;

class UpdateInstallmentPlan extends Config
{
    private $response;//request response
    public $Ozel_Oran_SK_ID;//ID of the list that coming from Special Ratio SK Liste
    public $MO_1;//One installment rate
    public $MO_2;//2nd Installment Rate
    public $MO_3;//3rd Installment Rate
    public $MO_4;//4rd Installment Rate
    public $MO_5;//5rd Installment Rate
    public $MO_6;//6rd Installment Rate
    public $MO_7;//7rd Installment Rate
    public $MO_8;//8rd Installment Rate
    public $MO_9;//9rd Installment Rate
    public $MO_10;//10rd Installment Rate
    public $MO_11;//11rd Installment Rate
    public $MO_12;//12rd Installment Rate

    /**
     * UpdateInstallmentPlan constructor.
     * @param $clientCode: Terminal ID, It will be forwarded by param.
     * @param $clientUsername: User Name, It will be forwarded by param.
     * @param $clientPassword: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     * @param $mode: string value TEST/PROD
     * @param $Ozel_Oran_SK_ID: ID of the list that coming from Special Ratio SK Liste
     * @param $MO1: One installment rate
     * @param $MO2: 2nd Installment Rate
     * @param $MO3: 3rd Installment Rate
     * @param $MO4: 4rd Installment Rate
     * @param $MO5: 5rd Installment Rate
     * @param $MO6: 6rd Installment Rate
     * @param $MO7: 7rd Installment Rate
     * @param $MO8: 8rd Installment Rate
     * @param $MO9: 9rd Installment Rate
     * @param $MO10: 10rd Installment Rate
     * @param $MO11: 11rd Installment Rate
     * @param $MO12: 12rd Installment Rate
     */
    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $Ozel_Oran_SK_ID,$MO1,$MO2,$MO3,$MO4,$MO5,$MO6,$MO7,$MO8,$MO9,$MO10,$MO11,$MO12)
    {
         
        parent::__construct($clientCode, $clientUsername, $clientPassword, $guid, $mode);
        $this->Ozel_Oran_SK_ID = $Ozel_Oran_SK_ID;
        $this->MO_1 = $MO1;
        $this->MO_2 = $MO2;
        $this->MO_3 = $MO3;
        $this->MO_4 = $MO4;
        $this->MO_5 = $MO5;
        $this->MO_6 = $MO6;
        $this->MO_7 = $MO7;
        $this->MO_8 = $MO8;
        $this->MO_9 = $MO9;
        $this->MO_10 = $MO10;
        $this->MO_11 = $MO11;
        $this->MO_12 = $MO12;
    }

    /**
     * send request to get the installments plan list for Merchant
     * @return array|bool
     */
    public function send()
    {
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
        $installmentsListObj = new TP_Ozel_Oran_SK_Guncelle($this->clientCode,$this->clientUsername,$this->clientPassword,$this->guid,
            $this->Ozel_Oran_SK_ID,
            $this->MO_1,
            $this->MO_2,
            $this->MO_3,
            $this->MO_4,
            $this->MO_5,
            $this->MO_6,
            $this->MO_7,
            $this->MO_8,
            $this->MO_9,
            $this->MO_10,
            $this->MO_11,
            $this->MO_12
        );
        $this->response = $client->TP_Ozel_Oran_SK_Guncelle($installmentsListObj);
    }

    /**
     * @return array result array
     */
    public function parse()
    {
        if(isset($this->response->TP_Ozel_Oran_SK_GuncelleResult) == False){
            return [
                'Sonuc' => -2,
                'Sonuc_Str' => 'Param response has wrong format',
            ];
        }else{
            return (array)$this->response->TP_Ozel_Oran_SK_GuncelleResult;
        }
    }

}