<?php
/**
 * Created by Payfull.
 * Date: 11/16/2018
 */

namespace param;

use param\paramBasics\TP_Islem_Iptal_Iade_Kismi;


class CancelRefund extends Config
{
    const REFUND_TYPE = 'IADE';
    const CANCEL_TYPE = 'IPTAL';
    const ERR_TRX = 'ERR_TRX';
    protected $response;//request response
    protected $transactionId;

    /**
     * CancelRefund constructor.
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
     * @param $type: For cancellation IPTAL For return IADE
     * @param $invoiceId: Transaction’s receipt ID.
     * @param $totalAmount: Cancellation / Return Amount, All amount must be written for CANCELLATION. All amount or smaller amount (partial) must be written for RETURN.
     * @param $transactionId: Single ID for current new transaction.
     */
    public function send($type, $invoiceId, $totalAmount, $transactionId)
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
        $cancelRefundObj = new TP_Islem_Iptal_Iade_Kismi($this->clientCode,$this->clientUsername,$this->clientPassword,$this->guid,$type,$invoiceId,$totalAmount);
        $this->response = $client->TP_Islem_Iptal_Iade_Kismi($cancelRefundObj);
    }

    /**
     * @return array result array
     */
    public function parse()
    {
        if(is_object($this->response) == False OR isset($this->response->TP_Islem_Iptal_Iade_KismiResult->Sonuc) == False)
        {
            return [
                'Sonuc' => -2,
                'Sonuc_Str' => 'Param response has wrong format',
            ];
        }
        else
        {
            return (array)$this->response->TP_Islem_Iptal_Iade_KismiResult;
        }
    }
}