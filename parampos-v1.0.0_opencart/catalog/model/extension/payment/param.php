<?php
class ModelExtensionPaymentParam extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/param');

		if ($this->config->get('payment_param_status')) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_param_standard_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
			if (!$this->config->get('payment_param_standard_geo_zone_id')) {
				$status = true;
			} elseif ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code' => 'param',
				'title' => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_param_sort_order')
			);
		}

		return $method_data;
	}

	public function addOrder($order_data) {

		$cap = '';
		if ($this->config->get('payment_param_transaction_method') == 'payment') {
			$cap = ",`capture_status` = '1'";
		}
		$this->db->query("INSERT INTO `" . DB_PREFIX . "param_order` SET `order_id` = '" . (int)$order_data['order_id'] . "', `created` = NOW(), `modified` = NOW(), `debug_data` = '" . $this->db->escape($order_data['debug_data']) . "', `amount` = '" . $this->currency->format($order_data['amount'], $order_data['currency_code'], false, false) . "', `currency_code` = '" . $this->db->escape($order_data['currency_code']) . "', `transaction_id` = '" . $this->db->escape($order_data['transaction_id']) . "'{$cap}");

		return $this->db->getLastId();
	}

	public function addTransaction($param_order_id, $type, $transactionid, $order_info) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "param_transactions` SET `param_order_id` = '" . (int)$param_order_id . "', `created` = NOW(), `transaction_id` = '" . $this->db->escape($transactionid) . "', `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "'");

		return $this->db->getLastId();
	}

	public function getCards($customer_id) {

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "param_card WHERE customer_id = '" . (int)$customer_id . "'");

		$card_data = array();

		$this->load->model('account/address');

		foreach ($query->rows as $row) {

			$card_data[] = array(
				'card_id' => $row['card_id'],
				'customer_id' => $row['customer_id'],
				'token' => $row['token'],
				'digits' => '**** ' . $row['digits'],
				'expiry' => $row['expiry'],
				'type' => $row['type'],
			);
		}
		return $card_data;
	}

	public function checkToken($token_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "param_card WHERE token_id = '" . (int)$token_id . "'");
		if ($query->num_rows) {
			return true;
		} else {
			return false;
		}
	}

	public function addCard($order_id, $card_data) {
		$this->db->query("INSERT into " . DB_PREFIX . "param_card SET customer_id = '" . $this->db->escape($card_data['customer_id']) . "', order_id = '" . $this->db->escape($order_id) . "', digits = '" . $this->db->escape($card_data['Last4Digits']) . "', expiry = '" . $this->db->escape($card_data['ExpiryDate']) . "', type = '" . $this->db->escape($card_data['CardType']) . "'");
	}

	public function updateCard($order_id, $token) {
		$this->db->query("UPDATE " . DB_PREFIX . "param_card SET token = '" . $this->db->escape($token) . "' WHERE order_id = '" . (int)$order_id . "'");
	}

	public function updateFullCard($card_id, $token, $card_data) {
		$this->db->query("UPDATE " . DB_PREFIX . "param_card SET token = '" . $this->db->escape($token) . "', digits = '" . $this->db->escape($card_data['Last4Digits']) . "', expiry = '" . $this->db->escape($card_data['ExpiryDate']) . "', type = '" . $this->db->escape($card_data['CardType']) . "' WHERE card_id = '" . (int)$card_id . "'");
	}

	public function deleteCard($order_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "param_card WHERE order_id = '" . (int)$order_id . "'");
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function getApiUrl() {
		if ($this->config->get('payment_param_test')) {
			$url = 'https://test-dmz.param.com.tr:4443/turkpos.ws/service_turkpos_test.asmx?wsdl';
		} else {
			$wsdlPath = DIR_SYSTEM . '/library/turkpos/ParamPOSApi.wsdl';
			if(file_exists($wsdlPath))
			{
				$url = $wsdlPath;	
			} else {
				$url = 'https://posws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl';
			}
		}
		return $url;
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
    public function getPaymentRequest($result, $secureString, $currencyCode) 
	{
		try {
			$client = $this->_createSoapClient($this->getApiUrl(), 0);
			$response = $client->SHA2B64(array('Data' => $secureString));
			$result->Islem_Hash = $response->SHA2B64Result;
			if($currencyCode == 'USD' || $currencyCode == 'EUR')
        	{
				$response = $client->TP_Islem_Odeme_WD($result);
				if($response->TP_Islem_Odeme_WDResult->Sonuc > 0){
					$this->response->redirect($response->TP_Islem_Odeme_WDResult->UCD_URL);
				} else {
					$this->session->data['error'] = $response->TP_Islem_Odeme_WDResult->Sonuc_Str;
					$this->response->redirect($this->url->link('checkout/checkout', '', true));
				}
			} else {
				$response = $client->Pos_Odeme($result);
				if($response->Pos_OdemeResult->Sonuc > 0){
					$this->response->redirect($response->Pos_OdemeResult->UCD_URL);
				} else {
					$this->session->data['error'] = $response->Pos_OdemeResult->Sonuc_Str;
					$this->response->redirect($this->url->link('checkout/checkout', '', true));
				}
			}
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
    }

	/**
     * Generate Paymnet Request
     * @return string
     */
    public function getPaymentRequestCurrceny($result, $secureString, $currencyCode) 
	{
		try {
			$client = $this->_createSoapClient($this->getApiUrl(), 0);
			$response = $client->SHA2B64(array('Data' => $secureString));
			$result->Islem_Hash = $response->SHA2B64Result;
			$response = $client->Pos_Odeme($result);
			if($response->Pos_OdemeResult->Sonuc > 0){
				$this->response->redirect($response->Pos_OdemeResult->UCD_URL);
			} else {
				$this->session->data['error'] = $response->Pos_OdemeResult->Sonuc_Str;
				$this->response->redirect($this->url->link('checkout/checkout', '', true));
			}
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
    }

	/**
	 * Get all installments
	 *
	 * @return void
	 */
	public function getInstallments($request)
	{
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		
		$request->BIN = (strlen($request->BIN) >= 6)?substr($request->BIN, 0, 6):'';
		$posId = $this->getBinPOS($request);
		
		unset($request->BIN);
		$request->GUID = $this->config->get('payment_param_guid');
		
		$quoteResponse = $this->getBinQuote($request);
		$installment = [];
		if($quoteResponse){
			foreach ($quoteResponse as $key => $resp) {
				if ($resp[0]["SanalPOS_ID"] == $posId['posId']) { 
					$installmentIndex = 12;
					for($i = 1; $i <= $installmentIndex; $i++) {
						$prerate = str_pad($i, 2, '0', STR_PAD_LEFT);
						$rate = $resp[0]["MO_$prerate"];
						if(floatval($rate) < 0)
					        continue;

                        $amount = (float) (1 + ($rate / 100)) *  $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
                        $fee = (float) ($rate / 100) * $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
						$installment[$i]['month'] = $prerate;
						$installment[$i]['rate'] = number_format($resp[0]["MO_$prerate"], 2);
						$installment[$i]['total_pay'] = number_format($amount, 2) . ' ' . $order_info['currency_code'];
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
		$client = $this->_createSoapClient($this->getApiUrl(), 0);
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
		$client = $this->_createSoapClient($this->getApiUrl(), 0);
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