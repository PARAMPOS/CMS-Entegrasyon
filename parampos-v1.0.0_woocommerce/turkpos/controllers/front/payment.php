<?php

class PaymentModuleFrontController extends ModuleFrontControllerCore
{

	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		$this->display_column_right = false;
		$this->bootstrap = true;
		$mp = false;
		parent::initContent();


		$tr = EticTransaction::createTransaction();
		$errBanka = false;
		$currency = new Currency($tr->id_currency);
		$cart = $this->context->cart;
		$customer = new Customer($cart->id_customer);
		$link = new LinkCore();
		$shop = Context::getContext()->shop;

		// if(version_compare(_PS_VERSION_, '1.7.0', '>=')){
		// $this->addJquery();
		// $this->registerJavascript(sha1('jquery.card.js') ,'/views/js/jquery.card.js');
		// $this->registerJavascript(sha1('jquery.payment.min.js') ,_MODULE_DIR_.$this->module->name.'/views/js/jquery.payment.min.js');
		// $this->registerJavascript(sha1('pro.js') ,_MODULE_DIR_.$this->module->name.'/views/js/pro.js');
		// $this->registerStylesheet(sha1('/views/css/payment.css'), _MODULE_DIR_.$this->module->name.'/views/css/payment.css');
		// $this->registerStylesheet(sha1('/views/css/jquery.card.css'), _MODULE_DIR_.$this->module->name.'/views/css/payment.css');
		// $this->registerStylesheet(sha1('/views/vendor/font-awesome/css/font-awesome.min.css'), _MODULE_DIR_.$this->module->name.'/views/vendor/font-awesome/css/font-awesome.min.css');
		// $this->registerStylesheet(sha1('/views/css/pro-form.css'), _MODULE_DIR_.$this->module->name.'/views/css/pro-form.css');
		// }
		// else {
		$this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.card.js', false);
		$this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.payment.min.js', false);
		$this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/pro.js', false);
		$this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/jquery.card.css', 'all');
		$this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/payment.css', 'all');
		$this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/pro-form.css', 'all');
		$this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/vendor/font-awesome/css/font-awesome.min.css', 'all');

		if (version_compare(_PS_VERSION_, '1.7.0', '<') === true) {
			$jq_uniform_paths = array(
				$shop->getBaseURI() . 'themes/' . _THEME_NAME_ . '/js/autoload/15-jquery.uniform-modified.js',
				$shop->getBaseURI() . 'themes/' . _THEME_NAME_ . '/js/formstyle.js',
				$shop->getBaseURI() . 'themes/default-bootstrap/js/autoload/15-jquery.uniform-modified.js'
			);
			foreach ($jq_uniform_paths as $p)
				$this->removeJS($p, false);
		}

		if (EticConfig::get("MASTERPASS_ACTIVE") == 'on') {
			include(_PS_MODULE_DIR_ . $this->module->name . '/lib/masterpass/EticsoftMasterPassLoader.php');
			$mp = new EticsoftMasterpass($tr);
			$mp->prepareUi();
			$this->addCss(_MODULE_DIR_ . $this->module->name . '/views/css/masterpass.css', 'all');
		}

		$card_rates = EticInstallment::getRates((float) $tr->total_cart);
		$restrictions = EticInstallment::getRestrictedProducts($tr->id_cart);
		if (is_array($restrictions) && !empty($restrictions))
			$card_rates = array();

		$this->context->smarty->assign(array(
			'mp' => $mp,
			'form_action' => $link->getModuleLink('sanalpospro', 'payment', array(), true),
			'template' => Configuration::get('POSPRO_ORDER_TMP'),
			'errBanka' => $errBanka,
			'curname' => new Currency(Configuration::get('SANAL_POS_CUR')),
			'sanalpospro_dir' => $this->module->dir,
			'sanalpospro_uri' => $this->module->uri,
			'currency_default' => Configuration::get('POSPRO_CUR'),
			'currency' => $currency,
			'cards' => $card_rates,
			'defaultins' => EticInstallment::calcDefaultRate((float) $tr->total_cart),
			'c_auto_currency' => Configuration::get('POSPRO_AUTO_CURRENCY'),
			'c_min_inst_amount' => (float) Configuration::get('POSPRO_MIN_INST_AMOUNT'),
			'auf' => Configuration::get('POSPRO_ORDER_AUTOFORM'),
			'is_17' => version_compare(_PS_VERSION_, '1.7.0', '>='),
		));

		if (EticConfig::get("MASTERPASS_ACTIVE") == 'on') {

			if (Etictools::getValue('mp_api_token') AND Etictools::getValue('mp_api_refno')) {
				$mpgw = new EticsoftMasterpassGateway($tr, Etictools::getValue('mp_api_refno'));
				$mpgw->apiPay();
				$tr = $mpgw->tr;
				if ($tr->result) {
					$currency = new Currency($tr->id_currency);
					$this->module->validateOrder($cart->id, $this->module->idOrderState, $cart->getOrderTotal(true, 3), $this->module->displayName . ' ' . $tr->gateway . ' ' . $tr->installment, null, array('transaction_id' => $tr->boid), $cart->id_currency, false, $customer->secure_key);
					$order = new Order($this->module->currentOrder);
					$tr->id_order = $this->module->currentOrder;
					$tr->save();
					Tools::redirectLink(__PS_BASE_URI__ . "order-confirmation.php?id_cart={$cart->id}&id_module={$this->module->id}&id_order={$this->module->currentOrder}&key={$order->secure_key}");
				}
				$this->context->smarty->assign(array(
					'errmsg' => $tr->result_code . ' ' . $tr->result_message,
					'errBanka' => true,
				));
				return $this->setTemplatex('payment_execution_' . Configuration::get('POSPRO_PAYMENT_PAGE') . '.tpl');
			}

			if (Etictools::getValue('mptd') AND Etictools::getValue('oid')) {
				$mpgw = new EticsoftMasterpassGateway($tr);
				$mpgw->tdValidate();
				$tr = $mpgw->tr;
				if ($tr->result) {
					$currency = new Currency($tr->id_currency);
					$this->module->validateOrder($cart->id, $this->module->idOrderState, $cart->getOrderTotal(true, 3), $this->module->displayName . ' ' . $tr->gateway . ' ' . $tr->installment, null, array('transaction_id' => $tr->boid), $cart->id_currency, false, $customer->secure_key);
					$order = new Order($this->module->currentOrder);
					$tr->id_order = $this->module->currentOrder;
					$tr->save();
					Tools::redirectLink(__PS_BASE_URI__ . "order-confirmation.php?id_cart={$cart->id}&id_module={$this->module->id}&id_order={$this->module->currentOrder}&key={$order->secure_key}");
				}
				$this->context->smarty->assign(array(
					'errmsg' => $tr->result_code . ' ' . $tr->result_message,
					'errBanka' => true,
				));
				return $this->setTemplatex('payment_execution_' . Configuration::get('POSPRO_PAYMENT_PAGE') . '.tpl');
			}
		}

		if (!Etictools::getValue('cc_number') AND ! Etictools::getValue('sprtdvalidate')) {
			return $this->setTemplatex('payment_execution_' . Configuration::get('POSPRO_PAYMENT_PAGE') . '.tpl');
		}				
		/**
			Chrome Cookie SameSite Policy fix 
			*/
			 
			$path = SameSiteCookieSetter::accessProtected(Context::getContext()->cookie, '_path');
			$domain = SameSiteCookieSetter::accessProtected(Context::getContext()->cookie, '_domain');
			foreach($_COOKIE as $k => $v){
				SameSiteCookieSetter::setcookie($k,$v, array('secure' => true, 'samesite' => 'None', 'path' => $path, 'domain' => $domain));
			}	
			/**
			Chrome Cookie SameSite Policy fix 
			*/


		$gateway = New EticGateway($tr->gateway);
		$lib_class_name = 'Eticsoft_' . $gateway->lib;
		$lib_class_path = dirname(__FILE__) . '/../../lib/gateways/' . $gateway->lib . '/' . $lib_class_name . '.php';
		$tr->debug("Try to include  " . $lib_class_name, true);
		include_once($lib_class_path);

		if (Etictools::getValue('sprtdvalidate')) {
			if ($exists = EticTransaction::getTransactionByCartId($cart->id)) {
				$tr->id_transaction = $exists['id_transaction'];
				$tr->__construct();
				$tr->exists = true;
			} else
				die("Cart not found");

			$lib = New $lib_class_name();
			$tr = $lib->tdValidate($tr);
			$tr->save();
		}
		else {
			$tr->createTransaction();
			$tr->debug("\n\n*********\n\n " . 'Form posted via ' . Configuration::get('POSPRO_PAYMENT_PAGE'));
			if (!$tr->validateTransaction()) {
				$this->context->smarty->assign(array(
					'errmsg' => $tr->result_code . ' ' . $tr->result_message,
					'errBanka' => true,
				));
				return $this->setTemplatex('payment_execution_' . Configuration::get('POSPRO_PAYMENT_PAGE') . '.tpl');
			}
			$lib = New $lib_class_name();
			$tr = $lib->pay($tr);
			$tr->save();
			if ($tr->tds AND $tr->tds_echo) {
				$this->context->smarty->assign(array(
					'tdsform' => $tr->tds_echo,
				));
				$tr->debug("3DS formu gönderiliyor");
				return $this->setTemplatex('payment_execution_tds.tpl');
			}
		}
		if ($tr->result) {
			$currency = new Currency($tr->id_currency);
			$this->module->validateOrder($cart->id, $this->module->idOrderState, $cart->getOrderTotal(true, 3), $this->module->displayName . ' ' . $tr->gateway . ' ' . $tr->installment, null, array('transaction_id' => $tr->boid), $cart->id_currency, false, $customer->secure_key);
			$order = new Order($this->module->currentOrder);
			$tr->id_order = $this->module->currentOrder;
			$tr->save();
			$tr->requestFraudScore();

			Tools::redirectLink(__PS_BASE_URI__ . "order-confirmation.php?id_cart={$cart->id}&id_module={$this->module->id}&id_order={$this->module->currentOrder}&key={$order->secure_key}");
		}
		$this->context->smarty->assign(array(
			'errmsg' => $tr->result_code . ' ' . $tr->result_message,
			'errBanka' => true,
		));
		return $this->setTemplatex('payment_execution_' . Configuration::get('POSPRO_PAYMENT_PAGE') . '.tpl');
	}

	public function setTemplateBefore($tpl_file)
	{
		return $this->setTemplatex($tpl_file);
	}

	public function setTemplatex($t)
	{
		if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
			return $this->setTemplate('module:sanalpospro/views/templates/front/17/' . $t);
		}
		return $this->setTemplate($t);
	}
}
