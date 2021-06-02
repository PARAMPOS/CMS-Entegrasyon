<?php
/**
 * Created by Payfull.
 * Date: 10/15/2018
 */

namespace param;

use param\paramBasics\TP_Islem_Sorgulama;

class TransactionQuery extends Config
{
    protected $response;//request response
    const ERR_TRX = 'ERR_TRX';
    protected $transactionId;

    /**
     * TransactionQuery constructor.
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
     * send transaction query to get the transaction latest status update
     * @param $invoiceId: Dekont_ID which is POSTed after successful transaction, optional.
     * @param $orderId: Posted Order ID after successful transaction.
     * @param $transactionId: Transaction ID sent to TP_Islem_Odeme method, optional.
     * @return array|bool
     */
    public function send($invoiceId, $orderId, $transactionId)
    {
        $this->transactionId = $transactionId;
        $client = new \SoapClient($this->serviceUrl);
        $queryObj = new TP_Islem_Sorgulama($this->clientCode, $this->clientUsername, $this->clientPassword, $this->guid, $invoiceId, $orderId, $transactionId);
        $this->response = $client->TP_Islem_Sorgulama($queryObj);
    }

    /**
     * @return array result array
     */
    public function parse()
    {
        $result = [
            'TURKPOS_RETVAL_Sonuc'=>'-1',
            'TURKPOS_RETVAL_Sonuc_Str'=>'',
            'TURKPOS_RETVAL_GUID'=>'',
            'TURKPOS_RETVAL_Islem_Tarih'=>'',
            'TURKPOS_RETVAL_Dekont_ID'=>'',
            'TURKPOS_RETVAL_Tahsilat_Tutari'=>'',
            'TURKPOS_RETVAL_Odeme_Tutari'=>'',
            'TURKPOS_RETVAL_Siparis_ID'=>'',
            'TURKPOS_RETVAL_Islem_ID'=>$this->transactionId,
            'TURKPOS_RETVAL_Ext_Data'=>'',
        ];

        //response has wrong format
        if(is_object($this->response) == False)
        {
            return [
                'Sonuc' => -2,
                'Sonuc_Str' => 'Param response has wrong format',
            ];
        }
        //query problem or transaction not found
        elseif($this->response->TP_Islem_SorgulamaResult->Sonuc == '0')
        {
            $result['TURKPOS_RETVAL_Sonuc_Str'] = $this->response->TP_Islem_SorgulamaResult->Sonuc_Str;
            $result['TURKPOS_RETVAL_Sonuc'] = $this->response->TP_Islem_SorgulamaResult->Sonuc;
        }
        //param give wrong format
        elseif(isset($this->response->TP_Islem_SorgulamaResult->DT_Bilgi->any) == False)
        {
            return $result;
        }


        $xml = $this->response->TP_Islem_SorgulamaResult->DT_Bilgi->any;
        $xmlStr = "<?xml version='1.0' standalone='yes'?><root>$xml</root>";
        $xmlStr    = str_replace(["diffgr:","msdata:"],'', $xmlStr);
        $data = @simplexml_load_string($xmlStr);


        //error in xml format
        if($data === False)
        {
            return $result;
        }

        //return the results same as 3d post results
        $transactionResult = (array)$data->diffgram->NewDataSet->DT_Islem_Sorgulama;
        $result = [
            'TURKPOS_RETVAL_Sonuc'=>$transactionResult['Odeme_Sonuc'],
            'TURKPOS_RETVAL_Sonuc_Str'=>$transactionResult['Odeme_Sonuc_Aciklama'],
            'TURKPOS_RETVAL_GUID'=>$this->guid,
            'TURKPOS_RETVAL_Islem_Tarih'=>$transactionResult['Tarih'],
            'TURKPOS_RETVAL_Dekont_ID'=>$transactionResult['Dekont_ID'],
            'TURKPOS_RETVAL_Tahsilat_Tutari'=>$transactionResult['Toplam_Tutar'],
            'TURKPOS_RETVAL_Odeme_Tutari'=>$transactionResult['Toplam_Tutar'],
            'TURKPOS_RETVAL_Siparis_ID'=>$transactionResult['Siparis_ID'],
            'TURKPOS_RETVAL_Islem_ID'=>$this->transactionId,
            'TURKPOS_RETVAL_Ext_Data'=>$transactionResult['Ext_Data'],
        ];
        return $result;
    }

}