<?php

class Transaction
{
	public $trId;
	public $exists;
	public $ccName;
	public $ccNumber;
	public $ccCVV;
	public $ccExpireYear;
	public $ccExpireMonth;
	public $gateway;
	public $cartId;
	public $orderId;
	public $customerId;
	public $cartTotalExcFee;
	public $cartTotalIncFee;
	public $shippingTotal;
	public $gatewayFee;
	public $installment;
	public $serviceUrl;
	public $customerFirstname;
	public $customerLastname;
	public $customerAddress;
	public $customerPhone;
	public $customerMobile;
	public $customerEmail;
	public $customerCity;
	public $currencyCode = 'TRY';
	public $currencyNumber = '949';
	public $ip;
	public $testMode;
	public $tds;
	public $boid;
	public $resultCode;
	public $resultMessage = 'Ödeme yapılmadı.';
	public $result;
	public $debug = '';
	public $trCreatedAt;
	public $trUpdatedAt;
	public $products = array();
	public $shopName;
	public $isoLang;
	public $failUrl;
	public $successUrl;
	public $gatewayParams;
	public $redirectUrl;

	function __construct($trId = false)
	{
		if ($this->trId AND ! $trId)
			$trId = $this->trId;

		if ($trId) {
			$this->trId = (int) $trId;
			if ($fields = $this->getById($this->trId)) {
				$this->exists = true;
				foreach ($fields as $k => $v)
					$this->{$k} = $v;
			}
		}

		WC()->session = new WC_Session_Handler();
		WC()->session->init();
		$orderId = WC()->session->get( 'order_awaiting_payment');
		$order = new WC_Order($orderId);

		$this->successUrl = add_query_arg(array('paramres' => 'success'), $order->get_checkout_order_received_url(true));
		$this->failUrl = add_query_arg(array('paramres' => 'fail'), $order->get_checkout_payment_url(true));
		$this->shopName = get_option('blogname');
		$this->isoLang = get_bloginfo("language");

		$this->trCreatedAt = date("Y-m-d H:i:s");
		$this->ip = Data::getClientIp();

	}

	/**
	 *
	 * @return void
	 */
	private function addTransaction()
	{
		global $wpdb;

		if ($this->exists)
			return false;

		$fields = $this->getFormated();
		if ($wpdb->insert(_DB_PREFIX_ . 'param_transaction', $fields)) {
			$this->exists = true;
			$this->trId = $wpdb->insert_id;
		}
		return false;
	}

	private function updateTransaction()
	{
		global $wpdb;

		if (!$this->exists OR ! $this->trId)
			return false;
		$fields = $this->getFormated();
		$fields['trUpdatedAt'] = date("Y-m-d H:i:s");
		$where = array('trId' => $this->trId);
		return $wpdb->update(_DB_PREFIX_ . 'param_transaction', $fields, $where);
	}

	public function saveTransaction()
	{
		if ($this->exists)
			return $this->updateTransaction();
		return $this->addTransaction();
	}

	private function getFormated()
	{
		$escaper = new Escaper();
		return array(
			'ccName' => $escaper->escapeHtml($this->ccName),
			'ccNumber' => Data::maskCcNo($this->ccNumber),
			'cartId' => $this->cartId,
			'currencyCode' => $this->currencyCode,
			'orderId' => $this->orderId,
			'customerId' => $this->customerId,
			'cartTotalExcFee' => (float) $this->cartTotalExcFee,
			'cartTotalIncFee' => (float) $this->cartTotalIncFee,
			'gatewayFee' => (float) $this->gatewayFee,
			'installment' => (int) $this->installment,
			'ip' => (string) $this->ip,
			'testMode' => (bool) $this->testMode,
			'tds' => (bool) $this->tds,
			'boid' => (string) $this->boid,
			'resultCode' => $escaper->escapeHtml($this->resultCode),
			'resultMessage' => $escaper->escapeHtml($this->resultMessage),
			'result' => (bool) $this->result,
			'trCreatedAt' => $this->trCreatedAt,
			'trUpdatedAt' => $this->trUpdatedAt,
		);
	}

	public function validateTransaction()
	{
		if (!$this->ccNumber OR ! $this->ccCVV OR ! $this->ccName OR ! $this->ccExpireMonth) {
			$this->resultCode = "-1";
			$this->resultMessage = "Kart Bilgileri Eksik veya Hatalı";
			return false;
		}
		if ((int) ('20' . substr($this->ccExpireYear, -2) . str_pad($this->ccExpireMonth, 2, 0, STR_PAD_LEFT)) < (int) date("Ym")) {
			$this->resultCode = "-2";
			$this->resultMessage = "Kart Son Kullanım Tarihi Hatalı "
				. (int) ('20' . substr($this->ccExpireYear, -2) . $this->ccExpireMonth) . ' - ' . date("Ym");
			return false;
		}
		if ($this->cartTotalExcFee < 0.1 OR $this->cartTotalIncFee < $this->cartTotalExcFee) {
			$this->resultCode = "-3";
			$this->resultMessage = "Sepet Toplamları Hatalı";
			return false;
		}
		return true;
	}

	/**
	 *
	 * @param [type] $orderId
	 * @return void
	 */
	public function getTransactionByOrderId($orderId)
	{
		global $wpdb;
		$query = "SELECT * FROM " . _DB_PREFIX_ . "param_transaction WHERE `orderId` = $orderId";
		return $wpdb->get_row($query);
	}

	/**
	 *
	 * @param [type] $cartId
	 * @return void
	 */
	public static function getTransactionByCartId($cartId)
	{
		global $wpdb;
		$query = "SELECT * FROM " . _DB_PREFIX_ . "param_transaction WHERE `cartId` = $cartId";
		return $wpdb->get_row($query);
	}

	/**
	 *
	 * @param [type] $trId
	 * @return void
	 */
	public static function getById($trId)
	{
		global $wpdb;
		$query = "SELECT * FROM " . _DB_PREFIX_ . "param_transaction WHERE `trId` = $trId";
		return $wpdb->get_row($q);
	}

	public static function createTransaction()
	{
		$orderId = WC()->session->get('order_awaiting_payment');
		$order = new WC_Order($orderId);
		$currency = Data::getCurrency($order->get_currency());
		$transaction = new Transaction();
		$installment = 1;
		$rate = 0;
		$fee = 0;

		if (!$orderId || !$order) {
			return false;
		}

		if ($exists = Transaction::getTransactionByCartId($orderId)) {
			$transaction->trId = $exists->trId;
			$transaction->__construct();
			$transaction->exists = true;
		}

		$transaction->cartTotalExcFee = $order->get_total();
		$transaction->currencyCode = $currency->iso_code;
		$transaction->currencyNumber = $currency->iso_code_num;
		$transaction->languageCode = 'tr';
		$transaction->cartId = $order->get_id();
		$transaction->customerId = $order->get_customer_id();
		$transaction->customerCity = $order->get_billing_city() ? $order->get_billing_city() : $order->get_shipping_city();
		$transaction->customerFirstname = $order->get_billing_first_name() ? $order->get_billing_first_name() : $order->get_shipping_first_name();
		$transaction->customerLastname = $order->get_billing_last_name() ? $order->get_billing_last_name() : $order->get_billing_last_name();
		$transaction->customerAddress = $order->get_billing_address_1() . ' ' . $order->get_billing_address_1();
		if(!$transaction->customerAddress OR $transaction->customerAddress == null){
			$transaction->customerAddress = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();
		}
		$transaction->customerEmail = $order->get_billing_email();
		$transaction->customerPhone = $order->get_billing_phone();
		$transaction->customerCompany = $order->get_billing_company();

		if (Data::getParam('cc_number')) {
			if($installmentData = Data::getParam('cc_installment')){
				$installmentData = explode('|', $installmentData);
				if(count($installmentData) == 3){
					$installment = $installmentData[0];
					$rate = $installmentData[1];
					$fee = $installmentData[2];
					$feeItem = new WC_Order_Item_Fee();
					$feeItem->set_amount($fee);
					$feeItem->set_total($fee);

                    if ( in_array( 'sifir-oran', WC_Tax::get_tax_class_slugs(), true ) ) {
                        $feeItem->set_tax_class('sifir-oran');
                    }
					else {
					    
                        $feeItem->set_tax_class('zero-rate');
                    }
					$feeItem->set_name(sprintf( __('Kredi kartı komisyon farkı %s taksit', 'woocommerce'), wc_clean($installment)));
					$order->add_item($feeItem);
					$order->calculate_totals(true);
				}
			}
			$transaction->installment = $installment;
			$transaction->cartTotalIncFee = (float) (1 + ($rate / 100)) * $transaction->cartTotalExcFee;
			$transaction->gatewayFee = (float) ($transaction->cartTotalIncFee - $transaction->cartTotalExcFee);

			$transaction->ccName = Data::getParam('cc_name');
			$transaction->ccNumber = str_replace(' ', '', Data::getParam('cc_number'));
			$transaction->ccCVV = Data::getParam('cc_cvv');
			if (Data::getParam('cc_expiry')) {
				$date = explode("/", Data::getParam('cc_expiry'));
				$transaction->ccExpireMonth = (int) $date[0];
				$transaction->ccExpireYear = substr((int) $date[1], -2);
			}
		}

		$transaction->successUrl = add_query_arg(array('paramres' => 'success'), $order->get_checkout_order_received_url(true));
		$transaction->failUrl = add_query_arg(array('paramres' => 'fail'), $order->get_checkout_payment_url(true));
		$transaction->shopName = get_option('blogname');
		$transaction->isoLang = get_bloginfo("language");

		$transaction->trCreatedAt = date("Y-m-d H:i:s");
		$transaction->ip = Data::getClientIp();
		$products = $order->get_items();
		foreach ($products as $product) {
			$transaction->products[] = array(
				'id_product' => $product['product_id'],
				'name' => strip_tags($product['name']),
				'price' => $product['line_total'],
				'quantity' => $product['qty'],
			);
		}
		return $transaction;
	}
}
