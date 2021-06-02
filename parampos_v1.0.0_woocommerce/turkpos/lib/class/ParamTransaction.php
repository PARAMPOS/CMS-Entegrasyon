<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParamTransaction
 *
 * @author mahmut
 */
class ParamTransaction
{

	public $id_transaction = false;
	public $exists = false;
	public $type = 'S';
	public $notify = false;
	//
	public $cc_name = false;
	public $cc_number = false;
	public $cc_cvv = false;
	public $cc_expire_year = false;
	public $cc_expire_month = false;
	//
	public $gateway = false;
	public $id_cart = false;
	public $id_currency = 1;
	public $id_order = false;
	public $id_customer = false;
	public $total_cart;
	public $total_pay;
	public $total_shipping;
	public $total_discount;
	public $gateway_fee;
	public $family;
	public $installment;
	public $serviceUrl;
	//
	public $customer_firstname = false;
	public $customer_lastname = false;
	public $customer_address = false;
	public $customer_phone = false;
	public $customer_mobile = false;
	public $customer_email = false;
	public $customer_city;
	//
	public $currency_code = 'TRY';
	public $currency_number = '949';
	//
	public $cip = false;
	public $test_mode = false;
	public $tds = false;
	public $boid; // gatewaya order id
	public $result_code = NULL;
	public $result_message = 'Ödeme yapılmadı';
	public $result = false;
	public $debug = '';
	public $detail = array();
	//
	public $date_create;
	public $date_update;
	public $product_list = array();
	//
	public $shop_name;
	public $iso_lang;
	public $fail_url;
	public $ok_url;
	//
	public $gateway_params;
	public $tds_echo = false;

	function __construct($id_transaction = false)
	{
		if ($this->id_transaction AND ! $id_transaction)
			$id_transaction = $this->id_transaction;

		if ($id_transaction) {
			$this->id_transaction = (int) $id_transaction;
			if ($fields = $this->getById($this->id_transaction)) {
				$this->exists = true;
				foreach ($fields as $k => $v)
					$this->{$k} = $v;
				if ($this->detail)
					$this->detail = unserialize($this->detail);
			}
		}
		WC()->session = new WC_Session_Handler();
		WC()->session->init();								   
		$id_order = WC()->session->get( 'order_awaiting_payment');
		$order = new WC_Order($id_order);

		$this->ok_url = add_query_arg(array('paramres' => 'success'), $order->get_checkout_order_received_url(true));
		$this->fail_url = add_query_arg(array('paramres' => 'fail'), $order->get_checkout_payment_url(true));
		$this->mptd_url = add_query_arg(array('mptd' => 'mptd'), $order->get_checkout_payment_url(true));
		$this->shop_name = get_option('blogname');
		$this->iso_lang = get_bloginfo("language");
		
		$this->date_create = date("Y-m-d H:i:s");
		$this->cip = ParamTools::getIp();

		if ($this->gateway) {
			$gateway = New ParamGateway($this->gateway);
			$this->gateway_params = $gateway->params;
			if (isset($gateway->params->test_mode))
				$this->test_mode = $gateway->params->test_mode == 'on' ? true : false;
		}
	}

	private function add()
	{
		if ($this->exists)
			return false;
		$fields = $this->getFormated();
		if (!$this->id_transaction = ParamQuery::insertRow('spr_transaction', $fields))
			return false;
		$this->exists = true;
		$this->saveDebug();
	}

	private function saveDebug()
	{
		if (ParamConfig::get('POSPRO_DEBUG_MOD') != 'on')
			return true;
		if (ParamQuery::getRow('spr_debug', 'id_transaction', $this->id_transaction))
			return $this->updateDebug();
		return $this->addDebug();
	}

	private function addDebug()
	{
		return ParamQuery::insertRow('spr_debug', array('debug' => $this->debug, 'id_transaction' => $this->id_transaction));
	}

	private function update()
	{
		if (!$this->exists OR ! $this->id_transaction)
			return false;
		$fields = $this->getFormated();
		$fields['date_update'] = date("Y-m-d H:i:s");
		$this->saveDebug();
		return ParamQuery::updateRow('spr_transaction', $fields, 'id_transaction', $this->id_transaction);
	}

	private function updateDebug()
	{
		return ParamQuery::updateRow('spr_debug', array('debug' => $this->getDebug() . $this->debug), 'id_transaction', $this->id_transaction);
	}

	public function save()
	{
		if ($this->exists)
			return $this->update();
		return $this->add();
	}

	public function debug($txt, $save_point = false)
	{

		$called = debug_backtrace(false)[1];
		if(!isset($called['line']))
			$called['line'] = 0;
		
		$this->debug .= date("Y/m/d h:i:s") . "\t|"
			. ParamQuery::fix($txt) . "|\t"
			. $called['class'] . $called['type'] . $called['function'] . ':' . $called['line'] . "\n";
		if ($save_point)
			$this->save();
	}

	public function getDebug()
	{
		if ($debug = ParamQuery::getRow('spr_debug', 'id_transaction', $this->id_transaction))
			return $debug['debug'];
		return null;
	}

	public function detail($k, $v)
	{
		$this->detail [$k] = $v;
	}

	public function getDetail($k)
	{
		return isset($this->detail[$k]) ? $this->detail[$k] : false;
	}

	private function getFormated()
	{
		return array(
			'notify' => $this->notify,
			'cc_name' => ParamTools::escape($this->cc_name),
			'cc_number' => ParamTools::maskCcNo($this->cc_number),
			'gateway' => $this->gateway,
			'id_cart' => $this->id_cart,
			'id_currency' => $this->id_currency,
			'id_order' => $this->id_order,
			'id_customer' => $this->id_customer,
			'total_cart' => (float) $this->total_cart,
			'total_pay' => (float) $this->total_pay,
			'gateway_fee' => (float) $this->gateway_fee,
			'installment' => (int) $this->installment,
			'cip' => (string) $this->cip,
			'test_mode' => (bool) $this->test_mode,
			'tds' => (bool) $this->tds,
			'boid' => (string) $this->boid,
			'result_code' => ParamTools::escape($this->result_code),
			'result_message' => ParamTools::escape($this->result_message),
			'result' => (bool) $this->result,
			'detail' => serialize($this->detail),
			'date_create' => $this->date_create,
			'date_update' => $this->date_update,
		);
	}

	public function validateTransaction()
	{
		
		if (!$this->cc_number OR ! $this->cc_cvv OR ! $this->cc_name OR ! $this->cc_expire_month) {
			$this->result = false;
			$this->result_code = "V0001";
			$this->result_message = "Kart Bilgileri Eksik veya Hatalı";
			return false;
		}
		if ((int) ('20' . substr($this->cc_expire_year, -2) . str_pad($this->cc_expire_month, 2, 0, STR_PAD_LEFT)) < (int) date("Ym")) {
			$this->result = false;
			$this->result_code = "V0002";
			$this->result_message = "Kart Son Kullanım Tarihi Hatalı "
				. (int) ('20' . substr($this->cc_expire_year, -2) . $this->cc_expire_month) . ' - ' . date("Ym");
			return false;
		}
		if ($this->total_cart < 0.1 OR $this->total_pay < $this->total_cart) {
			$this->result = false;
			$this->result_code = "V0003";
			$this->result_message = "Sepet Toplamları Hatalı";
			return false;
		}
		$this->debug('Validated Internal ');
		return true;
	}

	private function updateTransactionByOrderId($record)
	{
		ParamQuery::updateRow('spr_transaction', $record->databaseStructure(), 'id_record = ' . (int) $record['id_record'], 1);
	}

	private function updateTransactionByCartId($record)
	{
		ParamQuery::updateRow('spr_transaction', $record, 'id_cart = ' . (int) $record['id_cart'], 1);
	}

	public function getTransactionByOrderId($id_order)
	{
		return ParamQuery::getRow('spr_transaction', array('id_order' => (int) $id_order));
	}

	public function getTransactionByBoId($boid)
	{
		return ParamQuery::getRow('spr_transaction', array('boid' => $boid));
	}

	public static function getTransactionByCartId($id_cart)
	{
		return ParamQuery::getRow('spr_transaction', array('id_cart' => (int) $id_cart));
	}

	public static function getById($id_transaction)
	{
		return ParamQuery::getRow('spr_transaction', array('id_transaction' => (int) $id_transaction));
	}

	public static function createTransaction()
	{
		$id_order = WC()->session->get( 'order_awaiting_payment');
		$order = new WC_Order($id_order);
		$currency = ParamTools::getCurrency($order->get_currency());
		$tra = New ParamTransaction();
		$installment = 1;
		$rate = 0;
		$fee = 0;

		if (!$id_order || !$order) {
			$data = array();
			$data['Request'] = $_REQUEST;
			$data['Server'] = $_SERVER;
			die("invalid cart params");
		}
		
		if ($exists = ParamTransaction::getTransactionByCartId($id_order)) {
			$tra->id_transaction = $exists['id_transaction'];
			$tra->__construct();
			$tra->exists = true;
			$tra->detail('count', $tra->getDetail('count') + 1);
			// pre-rules for fraud
			// cnc =  card number change 
			// ipc =  ip change
			if ($tra->cc_number != ParamTools::maskCcNo(ParamTools::escape(str_replace(' ', '', ParamTools::getValue('cc_number')))))
				$tra->detail('cnc', $tra->getDetail('cnc') + 1);
			if ($tra->cip != ParamTools::getIp())
				$tra->detail('ipc', $tra->getDetail('ipc') + 1);
		}

		$tra->total_cart = $order->get_total();
		$tra->currency_code = $currency->iso_code;
		$tra->currency_number = $currency->iso_code_num;
		$tra->language_code = 'tr';
		$tra->id_cart = $order->get_id();
		$tra->id_customer = $order->get_customer_id();
		$tra->id_currency = $currency->iso_code_num;
		$tra->customer_city = $order->get_billing_city() ? $order->get_billing_city() : $order->get_shipping_city();
		$tra->customer_firstname = $order->get_billing_first_name() ? $order->get_billing_first_name() : $order->get_shipping_first_name();
		$tra->customer_lastname = $order->get_billing_last_name() ? $order->get_billing_last_name() : $order->get_billing_last_name();
		$tra->customer_address = $order->get_billing_address_1() . ' ' . $order->get_billing_address_1();
		if(!$tra->customer_address OR $tra->customer_address == null){
			$tra->customer_address = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();
		}
		$tra->customer_email = $order->get_billing_email();
		$tra->customer_mobile = ParamTools::formatMobile($order->get_billing_phone());
		$tra->customer_phone = $order->get_billing_phone();
		$tra->customer_company = $order->get_billing_company();
		$tra->customer_identify = null;
		
		if (!$tra->customer_phone AND $tra->customer_mobile)
			$tra->customer_phone = $tra->customer_mobile;

		if (!$tra->customer_mobile OR ! ParamTools::isMobile($tra->customer_mobile))
			$tra->customer_mobile = ParamTools::formatMobile($tra->customer_phone);

			
		if (ParamTools::getValue('cc_number')) {
			if($installmentData = ParamTools::getValue('cc_installment')){
				$installmentData = explode('|', $installmentData);
				if(count($installmentData) == 3){
					$installment = $installmentData[0];
					$rate = $installmentData[1];
					$fee = $installmentData[2];
					$feeItem = new WC_Order_Item_Fee();
					$feeItem->set_amount($fee);
					$feeItem->set_total($fee);
					$feeItem->set_name(sprintf( __( 'Kredi kartı komisyon farkı %s taksit', 'woocommerce' ), wc_clean( $installment ) ));
					$order->add_item($feeItem);
					$order->calculate_totals(true);
				}
			}
			$tra->installment = $installment; 
			$tra->total_pay = (float) (1 + ($rate / 100)) * $tra->total_cart;
			$tra->gateway_fee = (float) ($fee / 100) * $tra->total_pay;
			
			$tra->cc_name = ParamTools::escape(ParamTools::getValue('cc_name'));
			$tra->cc_number = ParamTools::escape(str_replace(' ', '', ParamTools::getValue('cc_number')));
			$tra->cc_cvv = ParamTools::escape(ParamTools::getValue('cc_cvv'));
			if (ParamTools::getValue('cc_expiry')) {
				$date = explode("/", ParamTools::escape(ParamTools::getValue('cc_expiry')));
				$tra->cc_expire_month = (int) $date[0];
				$tra->cc_expire_year = substr((int) $date[1], -2);
			}
		}

		$tra->ok_url = add_query_arg(array('paramres' => 'success'), $order->get_checkout_order_received_url(true));
		$tra->fail_url = add_query_arg(array('paramres' => 'fail'), $order->get_checkout_payment_url(true));
		$tra->mptd_url = add_query_arg(array('mptd' => 'mptd'), $order->get_checkout_payment_url(true));
		$tra->shop_name = get_option('blogname');
		$tra->iso_lang = get_bloginfo("language");
		
		$tra->date_create = date("Y-m-d H:i:s");
		$tra->cip = ParamTools::getIp();
		$products = $order->get_items();
		foreach ($products as $product) {
			$tra->product_list[] = array(
				'id_product' => $product['product_id'],
				'name' => strip_tags($product['name']),
				'price' => $product['line_total'],
				'quantity' => $product['qty'],
			);
		}
		return $tra;
	}

	public function requestFraudScore()
	{
		if (!$this->exists OR ! $this->id_transaction)
			return false;
		$data = $this;
		unset(
			$data->cc_cvv, $data->cc_expire_year, $data->cc_expire_month, $data->id_currency, $data->id_customer, $data->notify, $data->debug, $this->tds_echo, $this->gateway_params
		);
		$cli = New SanalPosApiClient($data->id_transaction);
		return $cli->validateRequest()
				->run($data)
				->getResponse();
	}

	public static function getAllRecords()
	{
		$sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'spr_transaction ORDER BY `date_create`';
		return Db::getInstance()->ExecuteS($sql);
	}

	public static function jsonMonthRecords()
	{
		$sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'spr_transaction GROUP BY YEAR(record_date)';
		$result = Db::getInstance()->ExecuteS($sql);
		$data = array();
		foreach ($result as $row) {
			$data[] = array(strftime("%m", strtotime($row['date_create'])), (float) $row['total_paid']);
		}
	}

	public function getShippingPrice()
	{
		$cart = New Cart($this->id_cart);
		return $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
	}

	public function getDiscounts()
	{
		$cart = New Cart($this->id_cart);
		return $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
	}

	public function validateTransactionResponse($orderId, $data)
	{
		$message = [
			'message' => '',
			'error' => false
		];
		if(!isset($data['TURKPOS_RETVAL_Sonuc']) || (isset($data['TURKPOS_RETVAL_Sonuc']) && intval($data['TURKPOS_RETVAL_Sonuc']) < 1)) {
			$message['error'] = true;
			$message['message'] = $data['TURKPOS_RETVAL_Sonuc_Str'];
		}
		if($orderId !== intval($data['TURKPOS_RETVAL_Siparis_ID'])) {
			$message['error'] = true;
			$message['message'] = 'Hatalı Sipariş';
		}

		return $message;
	}
}
