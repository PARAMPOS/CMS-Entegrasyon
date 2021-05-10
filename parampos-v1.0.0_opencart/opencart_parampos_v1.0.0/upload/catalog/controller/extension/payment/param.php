<?php
class ControllerExtensionPaymentParam extends Controller {
	public function index() {
		$this->load->language('extension/payment/param');

		$data['payment_type'] = $this->config->get('payment_param_payment_type');

		$data['months'] = array();

		for ($i = 1; $i <= 12; $i++) {
			$data['months'][] = array(
				'text'  => sprintf('%02d', $i),
				'value' => sprintf('%02d', $i)
			);
		}

		$today = getdate();

		$data['year_expire'] = array();

		for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
			$data['year_expire'][] = array(
				'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
				'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
			);
		}

		if ($this->config->get('payment_param_test')) {
			$data['text_testing'] = $this->language->get('text_testing');
			$data['Endpoint'] = 'Sandbox';
		} else {
			$data['text_testing'] = '';
			$data['Endpoint'] = 'Production';
		}
		if ($this->config->get('payment_param_installment_status')) {
			$data['installment'] = true;
		} else {
			$data['installment'] = false;
		}

		$this->load->model('extension/payment/param');
		$template = 'param';
		$data['action'] = $this->url->link('extension/payment/param/callback', 'AccessCode=' . 999, true);
		$data['AccessCode'] = 999;
		$data['InstallmentUrl'] = $this->url->link('extension/payment/param/installment', '', true);
		return $this->load->view('extension/payment/' . $template, $data);
	}

	public function lowestDenomination($value, $currency) {
		$power = $this->currency->getDecimalPlace($currency);

		$value = (float)$value;

		return (int)($value * pow(10, $power));
	}

	public function ValidateDenomination($value, $currency) {
		$power = $this->currency->getDecimalPlace($currency);
		
		$value = (float)$value;
		return (int)($value * pow(10, '-' . $power));
	}

	public function callback() {
		$this->load->language('extension/payment/param');
		if (isset($this->request->get['AccessCode']) || isset($this->request->get['amp;AccessCode'])) {
			$this->load->model('extension/payment/param');

			if (isset($this->request->get['AccessCode'])) {
				$this->load->model('checkout/order');
				$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
				$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

				if ($this->config->get('payment_param_test')) {
					$data['text_testing'] = $this->language->get('text_testing');
					$data['Endpoint'] = 'Sandbox';
				} else {
					$data['text_testing'] = '';
					$data['Endpoint'] = 'Production';
				}

				$this->load->model('localisation/zone');

				$payment_zone_info = $this->model_localisation_zone->getZone($order_info['payment_zone_id']);
				$payment_zone_code = isset($payment_zone_info['code']) ? $payment_zone_info['code'] : '';

				$shipping_zone_info = $this->model_localisation_zone->getZone($order_info['shipping_zone_id']);
				$shipping_zone_code = isset($shipping_zone_info['code']) ? $shipping_zone_info['code'] : '';

				$result = new stdClass();
				$result->SanalPOS_ID = '';
				$result->G = new stdClass();
				$result->G->CLIENT_CODE = $this->config->get('payment_param_client_code');
				$result->G->CLIENT_USERNAME = $this->config->get('payment_param_client_username');
				$result->G->CLIENT_PASSWORD = $this->config->get('payment_param_client_password');
				$result->GUID = $this->config->get('payment_param_guid');
				$result->KK_Sahibi = $this->request->post['PARAM_CARDNAME'];
				$result->KK_No = $this->request->post['PARAM_CARDNUMBER'];
				$result->KK_CVC = $this->request->post['PARAM_CARDCVN'];
				$result->KK_SK_Ay = $this->request->post['PARAM_CARDEXPIRYMONTH'];
				$result->KK_SK_Yil = $this->request->post['PARAM_CARDEXPIRYYEAR'];
				$result->KK_Sahibi_GSM = '';
				$result->Hata_URL = $this->url->link('extension/payment/param/callback', '', true);
				$result->Basarili_URL = $this->url->link('extension/payment/param/callback', '', true);
				$result->Siparis_ID = $this->session->data['order_id'];
				$result->Siparis_Aciklama = date("d-m-Y H:i:s") . " tarihindeki ödeme";
				if(isset($this->request->post['PARAM_INSTALLMENT']) &&  count($installment = explode('|', ($this->request->post['PARAM_INSTALLMENT']))) == 3)
				{
					$result->Taksit = $installment[0];
					$rate = $installment[1];
					$amount = (float) (1 + ($rate / 100)) *  $amount;
					$fee = (float) ($rate / 100) * $amount;
					$message = 'Takist: ' . $installment[0]. "\n";
					$message .= 'Komisyon Oranı: %' . $installment[1] . "\n";
					$message .= 'Komisyon Tutarı: ' . number_format(round($fee, 2), 2, ',', '') . $order_info['currency_code'] . "\n";
					$message .= 'Tahsil Edilen Toplam Tutar: ' 	. number_format(round($amount, 2), 2, ',', '') . $order_info['currency_code'] . "\n";
					$result->Data2 = $message;

				} else 
				{
					$result->Taksit = '1';
					$result->Data2 = '';
				}
				
				$result->Islem_Tutar = number_format(round($amount, 2), 2, ',', '');
				$result->Toplam_Tutar = number_format(round($amount, 2), 2, ',', '');
				$result->Islem_Hash = '';
				$result->Islem_Guvenlik_Tip = '3D';
				$result->Islem_ID = str_replace(".","",microtime(true)).rand(000,999);
				$result->IPAdr = $_SERVER['REMOTE_ADDR'];
				$result->Ref_URL =  $this->url->link('checkout/checkout', '', true);
				$result->Data1 = base64_encode(json_encode(array(
					'Last4Digits' => substr(str_replace(' ', '', $this->request->post['PARAM_CARDNUMBER']), -4, 4),
					'ExpiryDate' => $this->request->post['PARAM_CARDEXPIRYMONTH'] . '/' . $this->request->post['PARAM_CARDEXPIRYYEAR']
				)));
				$result->Data3 = '';
				$result->Data4 = '';
				$result->Data5 = '';
				$result->Data6 = '';
				$result->Data7 = '';
				$result->Data8 = '';
				$result->Data9 = '';
				$result->Data10 = '';
				//Dim Islem_Guvenlik_Str$ = CLIENT_CODE & GUID & Taksit & Islem_Tutar & Toplam_Tutar & Siparis_ID & Hata_URL & Basarili_URL
				$Islem_Guvenlik_Str = $result->G->CLIENT_CODE . $result->GUID . $result->Taksit . $result->Islem_Tutar . $result->Toplam_Tutar . $result->Siparis_ID . $result->Hata_URL . $result->Basarili_URL;
				$response = $this->model_extension_payment_param->getPaymentRequest($result, $Islem_Guvenlik_Str);
			}
		} elseif (isset($this->request->post['TURKPOS_RETVAL_Sonuc'])) 
		{
			if ($this->request->post['TURKPOS_RETVAL_Sonuc'] < 0) 
			{
				$this->session->data['error'] = $this->request->post['TURKPOS_RETVAL_Sonuc_Str'];
				$this->log->write('Param POS error: ' . $this->request->post['TURKPOS_RETVAL_Sonuc_Str']);
				$this->response->redirect($this->url->link('checkout/checkout', '', true));
			} else 
			{
				if(isset($this->request->post['TURKPOS_RETVAL_Sonuc']) && $this->request->post['TURKPOS_RETVAL_Sonuc'] == 1) 
				{
					
					$this->load->model('checkout/order');
					
					$order_id = $this->request->post['TURKPOS_RETVAL_Siparis_ID'];
					$order_info = $this->model_checkout_order->getOrder($order_id);
					
					$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
					$transcactionId = $this->request->post['TURKPOS_RETVAL_Islem_ID'];
					$docId = $this->request->post['TURKPOS_RETVAL_Dekont_ID'];
					$responseMessage = $this->request->post['TURKPOS_RETVAL_Sonuc_Str'];
					$extraData = explode('|', $this->request->post['TURKPOS_RETVAL_Ext_Data']);

					$this->load->model('extension/payment/param');
					
					$param_order_data = array(
						'order_id' => $order_id,
						'transaction_id' => $transcactionId,
						'amount' => $this->ValidateDenomination($amount, $order_info['currency_code']),
						'currency_code' => $order_info['currency_code'],
						'debug_data' => ''
					);
					$param_order_id = $this->model_extension_payment_param->addOrder($param_order_data);
					$this->model_extension_payment_param->addTransaction($param_order_id, $this->config->get('payment_param_transaction_method'), $transcactionId, $order_info);
					
					$message = "Param POS Payment accepted\n";
					$message .= 'ISLEM ID: ' . $transcactionId. "\n";
					$message .= 'DEKONT ID: ' . $docId . "\n";
					$message .= 'Response: ' . $responseMessage . "\n";
					if(isset($extraData[0]) && !empty($extraData[0]))
					{
						$additionalData = json_decode(base64_decode($extraData[0]), true);
						$message .= 'Last4Digits: ' . $additionalData['Last4Digits'] . "\n";
						$message .= 'ExpiryDate: ' . $additionalData['ExpiryDate'] . "\n";
					}

					if(isset($extraData[1]) && !empty($extraData[1]))
					{
						$message .= 'Taksit Bilgileri: ' . "\n" . $extraData[1];
					}

					if ($this->config->get('payment_param_transaction_method') == 'payment') 
					{
						$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_param_order_status_id'), $message);
					} else 
					{
						$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_param_order_status_auth_id'), $message);
					}
					if ($this->customer->isLogged())
					{
						$card_data = array();
						if(isset($additionalData))
						{
							$card_data['Last4Digits'] = $additionalData['Last4Digits'];
							$card_data['ExpiryDate'] = $additionalData['ExpiryDate'];
						}
						$card_data['customer_id'] = $this->customer->getId();
						$card_data['Token'] = $this->request->post['TURKPOS_RETVAL_Hash'];
						$card_data['CardType'] = '';
						$this->model_extension_payment_param->addCard($this->session->data['order_id'], $card_data);
					}
				}
				$this->response->redirect($this->url->link('checkout/success', '', true));
			}
		}
	}

	public function installment() {
		if(!empty($this->request->post['ccnumber']) && isset($this->request->post['ccnumber']))
		{
			$this->load->model('extension/payment/param');

			$ccNumber = $this->request->post['ccnumber'];
			$ccNumber = str_replace(' ', '', $ccNumber);
			$result = new stdClass();
			$result->G = new stdClass();
			$result->G->CLIENT_CODE = $this->config->get('payment_param_client_code');
			$result->G->CLIENT_USERNAME = $this->config->get('payment_param_client_username');
			$result->G->CLIENT_PASSWORD = $this->config->get('payment_param_client_password');
			$result->BIN = $ccNumber;
			$modelPaymentParam = $this->model_extension_payment_param->getInstallments($result);

			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput($modelPaymentParam);
		}
	}
}