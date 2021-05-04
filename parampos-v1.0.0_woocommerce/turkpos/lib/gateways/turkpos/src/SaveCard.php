<?php
/**
 * Created by Payfull.
 * Date: 10/16/2018
 */

namespace param;

use param\paramBasics\KS_Kart_Ekle;

class SaveCard extends Config
{
    protected $response;//request response
    public $guid;//Key Belonging to Member Workplace
    public $clientCode;//Terminal ID, It will be forwarded by param.
    public $clientUsername;//User Name, It will be forwarded by param.
    public $clientPassword;//Password, It will be forwarded by param.
    public $receiverCardNumber;//Card Number Belonging to Member Workplace
    public $cardHolder;//Credit Card Holder
    public $cardNumber;//Credit Card Number
    public $cardExpMonth;//Last 2 digit Expiration month
    public $cardExpYear;//4 digit Expiration Year
    public $cvc;//CVC Code

    /**
     * SaveCard constructor.
     * @param $clientCode: Terminal ID, It will be forwarded by param.
     * @param $clientUsername: User Name, It will be forwarded by param.
     * @param $clientPassword: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     * @param $mode: string value TEST/PROD
     */
    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode)
    {
        parent::__construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, true);
    }

    /**
     * @param $receiverCardNumber: Card Number Belonging to Member Workplace
     * @param $cardHolder: Credit Card Holder
     * @param $cardNumber: Credit Card Number
     * @param $cardExpMonth: Last 2 digit Expiration month
     * @param $cardExpYear: 4 digit Expiration Year
     * @param $cvc: CVC Code
     */
    public function send($cardHolder, $cardNumber,
                         $cardExpMonth, $cardExpYear, $cvc)
    {
        $client = new \SoapClient($this->serviceUrl);

        $saveCardObj = new KS_Kart_Ekle($this->clientCode,$this->clientUsername,$this->clientPassword, $this->guid, $cardHolder, $cardNumber,
            $cardExpMonth, $cardExpYear, $cvc);
        $this->response = $client->KS_Kart_Ekle($saveCardObj);
    }

    /**
     * @return array result array
     */
    public function parse()
    {
        if(isset($this->response->KS_Kart_EkleResult) == False){
            return [
                'Sonuc' => -2,
                'Sonuc_Str' => 'Param response has wrong format',
            ];
        }else{
            return (array)$this->response->KS_Kart_EkleResult;
        }
    }

}