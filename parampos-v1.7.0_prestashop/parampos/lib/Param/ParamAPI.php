<?php
/**
 * A PHP Param Rapid API library implementation.
 *
 * @version   1.0.0
 * @package   Param
 * @author    Param www.param.com.tr
 */

/**
 * Param Library
 *
 * @package PARAM
 */
class ParamAPI
{

    private $url;
    private $sandbox;
    private $clientCode;
    private $clientUsername;
    private $clientPassword;
    private $guid;

    /**
     * ParamAPI constructor
     *
     * $params['sandbox'] to true to use the sandbox for testing
     */
    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $params = array())
    {
        if (Tools::strlen($clientCode) === 0 || 
            Tools::strlen($clientUsername) === 0 || 
            Tools::strlen($clientPassword) === 0 || 
            Tools::strlen($guid) === 0)  
        {
            Logger::addLog('Param Username & Password not configured', 4, null);
            die('Username and Password are required');
        }

        $this->clientCode = $clientCode;
        $this->clientUsername = $clientUsername;
        $this->clientPassword = $clientPassword;
        $this->guid = $guid;

        if (count($params) && isset($params['sandbox']) && $params['sandbox']) {
            $this->url = 'https://test-dmz.param.com.tr:4443/turkpos.ws/service_turkpos_test.asmx?wsdl';
            $this->sandbox = true;
        } else {
            $wsdlPath = _PS_MODULE_DIR_ . 'parampos/lib/wsdl/ParamPOSApi.wsdl';
            if(file_exists($wsdlPath)){
                $this->url = $wsdlPath;
            } else {
                $this->url = 'https://posws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl';
            }
            $this->sandbox = false;
        }
    }

    /**
     * Create soap client with selected wsdl
     *
     * @param string $wsdl
     * @param bool|int $trace
     * @return \SoapClient
     */
    protected function _createSoapClient($wsdl, $trace = false)
    {
        $client = new \SoapClient($wsdl, array(
			'trace' => 1,
			"encoding"=>"UTF-8",
			'stream_context' => stream_context_create(array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false, 
                    'allow_self_signed' => true,
                    'crypto_method' =>  STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                ))
            ))
        );
        return $client;
    }

	/**
     * Generate Paymnet Request
     * @return string
     */
    public function getPaymentRequest($result, $secureString) 
	{


           global $currency;
           $my_currency_iso_code = $currency->iso_code;


           if ($currency->iso_code == 'TRY') {


           	try {
			$client = $this->_createSoapClient($this->url, 0);
			$response = $client->SHA2B64(array('Data' => $secureString));
			$result->Islem_Hash = $response->SHA2B64Result;
			$response = $client->Pos_Odeme($result);
			if($response->Pos_OdemeResult->Sonuc > 0){
                Tools::redirect($response->Pos_OdemeResult->UCD_URL);
			} else {
                $checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ?
                'order-opc' : 'order';

                $url = _PS_VERSION_ >= '1.5' ?
                    'index.php?controller='.$checkout_type.'&' : $checkout_type.'.php?';

                $url .= 'step=3&cgv=1&paramerror=1&message='.$response->Pos_OdemeResult->Sonuc_Str;

                if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 'order-opc') {
                    $url.'#param';
                }
                Tools::redirect($url);
			}
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}

		
		}

		else {


try {
			$client = $this->_createSoapClient($this->url, 0);
			$response = $client->SHA2B64(array('Data' => $secureString));
			$result->Islem_Hash = $response->SHA2B64Result;
			$response = $client->TP_Islem_Odeme_WD($result);
			if($response->TP_Islem_Odeme_WDResult->Sonuc > 0){
                Tools::redirect($response->TP_Islem_Odeme_WDResult->UCD_URL);
			} else {
                $checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ?
                'order-opc' : 'order';

                $url = _PS_VERSION_ >= '1.5' ?
                    'index.php?controller='.$checkout_type.'&' : $checkout_type.'.php?';

                $url .= 'step=3&cgv=1&paramerror=1&message='.$response->TP_Islem_Odeme_WDResult->Sonuc_Str;

                if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 'order-opc') {
                    $url.'#param';
                }
                Tools::redirect($url);
			}
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}





		}
    }

    /**
	 * Get all installments
	 *
	 * @return void
	 */
	public function getInstallments($request, $cart)
	{
        $amount = number_format($cart->getOrderTotal(), 2, '.', '');
        $amountx = number_format($cart->getOrderTotal(), 2, '.', '');
		$currency = new Currency((int)$cart->id_currency);
		$request->BIN = (strlen($request->BIN) >= 6)?substr($request->BIN, 0, 6):'';
		$posId = $this->getBinPOS($request);
		
		unset($request->BIN);
		$request->GUID = Configuration::get('PARAM_GUID');
		
		$quoteResponse = $this->getBinQuote($request);
		
		$installment = [];
		if($quoteResponse){
			foreach ($quoteResponse as $key => $resp) {
				if ($resp[0]["SanalPOS_ID"] == $posId['posId']) { 
					$installmentIndex = 12;
					for($i = 1; $i <= $installmentIndex; $i++) {
						$prerate = str_pad($i, 2, '0', STR_PAD_LEFT);
						$rate = $resp[0]["MO_$prerate"];
						if(!floatval($rate) || floatval($rate) < 0)
							continue;
						
						$amount = (float) (1 + ($rate / 100)) *  $amountx;
						$fee = (float) ($rate / 100) * $amount;
						$installment[$i]['month'] = $prerate;
						$installment[$i]['rate'] = number_format($resp[0]["MO_$prerate"], 2);
						$installment[$i]['total_pay'] = number_format($amount, 2) . ' ' . $currency->iso_code;
						$installment[$i]['fee'] = number_format($fee, 2);
					}
				}
			}
		}
		return json_encode($installment);
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $request
	 * @return void
	 */
	private function getBinPOS($request){
		$client = $this->_createSoapClient($this->url, 0);
        $response = $client->BIN_SanalPos($request);
		if(isset($response->BIN_SanalPosResult))
		{
			$binResponse = [];
			$q1 = $response->BIN_SanalPosResult;
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
				$binResponse[] = [
					'bin' => $card['BIN'],
					'posId' => $card['SanalPOS_ID'],
					'posName' => $card['Kart_Banka'],
				];
				if($request->BIN != ''){
					return $binResponse[0];
				}
			}

			return $binResponse;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $request
	 * @return void
	 */
	private function getBinQuote($request)
	{
		$client = $this->_createSoapClient($this->url, 0);
		$quoteResponse = $client->TP_Ozel_Oran_SK_Liste($request);
		if(isset($quoteResponse->TP_Ozel_Oran_SK_ListeResult))
		{
			$q1 = $quoteResponse->TP_Ozel_Oran_SK_ListeResult;
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
			foreach ($list->DT_Ozel_Oranlar_SK as $instData){
				$installmentsArr[strtoupper($instData->Kredi_Karti_Banka)] = [(array)$instData];
			}
			return $installmentsArr;
		}
	}

}
