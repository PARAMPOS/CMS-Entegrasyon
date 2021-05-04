<?php
/**
 * Param Prestashop Payment Module
 *
 * @author    Param www.param.com.tr
 * @version   1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Parampos extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'parampos';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Param';

        $this->module_key = "a3123eaf51d2d7869e8a0542b406be2b";
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array(
            'min' => '1.5',
            'max' => _PS_VERSION_
        );

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Param Payments');
        $this->description = $this->l('Accepts payments with Param POS!');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->payment_types = array(
            'visa' => $this->l('Visa'),
            'mastercard' => $this->l('MasterCard'),
            'amex' => $this->l('American Express')
        );
    }

    public function install()
    {
        /* The cURL PHP extension must be enabled to use this module */
        if (!function_exists('curl_version')) {
            $this->_errors[] = $this->l(
                'Sorry, this module requires the cURL PHP '
                .'Extension (http://www.php.net/curl), which is not enabled '
                .'on your server. Please ask your hosting provider for '
                .'assistance.'
            );
            return false;
        }

        if (!parent::install()
                || !Configuration::updateValue('PARAM_CLIENT_CODE', '')
                || !Configuration::updateValue('PARAM_CLIENT_USERNAME', '')
                || !Configuration::updateValue('PARAM_CLIENT_PASSWORD', '')
                || !Configuration::updateValue('PARAM_GUID', '')
                || !Configuration::updateValue('PARAM_SANDBOX', 1)
                || !Configuration::updateValue('PARAM_PAYMENTTYPE', 'visa,mastercard')
                || !Configuration::updateValue('PARAM_PAYMENTMETHOD', 'transparent')
                || !$this->registerHook('payment')
                || !$this->registerHook('paymentReturn')
                || !$this->registerHook('backOfficeHeader')
                || !$this->registerHook('displayHeader')) {
            $this->_errors[] = $this->l('There was an Error installing the module.');
            return false;
        }

        if (_PS_VERSION_ >= '1.7' && (!$this->registerHook('paymentOptions'))) {
            $this->_errors[] = $this->l('There was an Error installing the module.');
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('PARAM_CLIENT_CODE')
                || !Configuration::updateValue('PARAM_CLIENT_USERNAME', '')
                || !Configuration::updateValue('PARAM_CLIENT_PASSWORD', '')
                || !Configuration::updateValue('PARAM_GUID', '')
                || !Configuration::deleteByName('PARAM_SANDBOX')
                || !Configuration::deleteByName('PARAM_PAYMENTTYPE')
                || !Configuration::deleteByName('PARAM_PAYMENTMETHOD')
                || !parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function hookBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/parampos.css');
    }

    public function getContent()
    {
        $this->postProcess();

        $this->context->smarty->assign(array(
            'module_dir' => $this->_path
        ));

        $html = $this->display(__FILE__, 'views/templates/admin/back_office.tpl');
        return $html.$this->displayForm();
    }

    private function displayForm()
    {

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $lang->id;
        $helper->identifier = $this->identifier;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitRapidParam';
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        $fields_form = array(
            'form' => array(
                'legend' => array(
                  'title' => $this->l('Param Settings'),
                  'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => (_PS_VERSION_ < '1.6' ? 'radio':'switch'),
                        'label' => $this->l('Sandbox mode'),
                        'name' => 'sandbox',
                        'is_bool' => true,
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'client_code',
                        'label' => $this->l('Client Code'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'client_username',
                        'label' => $this->l('Client Username'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'client_password',
                        'label' => $this->l('Client Password'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'guid',
                        'label' => $this->l('GUID'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Payment method'),
                        'name' => 'paymentmethod',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => 'transparent',
                                    'name' => $this->l('Transparent Redirect'),
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => $this->l('Payment Types'),
                        'name' => 'paymenttype',
                        'required' => true,
                        'values'  => array(
                            'query' => $this->getPaymentTypeFields(),
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6' ? 'radio':'switch'),
                        'label' => $this->l('Enable Installment'),
                        'name' => 'installment',
                        'is_bool' => true,
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right button',
                )
            )
        );

        $types = explode(',', Configuration::get('PARAM_PAYMENTTYPE'));
        $typeFields = array();
        foreach ($types as $id) {
            $typeFields['paymenttype_'.$id] = 'on';
        }

        $helper->tpl_vars = array(
            'fields_value' => $this->getFieldValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    private function getPaymentTypeFields()
    {
        $types = array();
        foreach ($this->payment_types as $id => $name) {
            $types[] = array(
                    'id_option' => $id,
                    'name' => $name,
            );
        }
        return $types;
    }

    private function getFieldValues()
    {
        $types = explode(',', Configuration::get('PARAM_PAYMENTTYPE'));
        $typeFields = array();
        foreach ($types as $id) {
            $typeFields['paymenttype_'.$id] = 'on';
        }
        
        return array(
                'sandbox'           => Configuration::get('PARAM_SANDBOX'),
                'client_code'       => Configuration::get('PARAM_CLIENT_CODE'),
                'client_username'   => Configuration::get('PARAM_CLIENT_USERNAME'),
                'client_password'   => Configuration::get('PARAM_CLIENT_PASSWORD'),
                'guid'              => Configuration::get('PARAM_GUID'),
                'paymentmethod'     => Configuration::get('PARAM_PAYMENTMETHOD'),
                'installment'     => Configuration::get('PARAM_INSTALLMENT'),
            ) + $typeFields;
    }
    
    private function postProcess()
    {
        if (Tools::isSubmit('submitRapidParam')) {
            $post_errors = array();

            if (!Tools::getValue('client_code')) {
                $post_errors[] = $this->l('Param Client Code cannot be empty');
            }

            if (!Tools::getValue('client_username')) {
                $post_errors[] = $this->l('Param Client Username cannot be empty');
            }

            if (!Tools::getValue('client_password')) {
                $post_errors[] = $this->l('Param Client Password cannot be empty');
            }

            if (!Tools::getValue('guid')) {
                $post_errors[] = $this->l('Param GUID cannot be empty');
            }

            $types = array();
            foreach (array_keys($this->payment_types) as $id) {
                if (Tools::getValue('paymenttype_'.$id)) {
                    $types[] = $id;
                }
            }

            if (empty($types)) {
                $post_errors[] = $this->l('You need to accept at least 1 payment type');
            }

            if (!Tools::getValue('paymentmethod')) {
                $post_errors[] = $this->l('Please select a payment method');
            }

            if (empty($post_errors)) {
                Configuration::updateValue('PARAM_SANDBOX', (int)Tools::getValue('sandbox'));
                Configuration::updateValue('PARAM_CLIENT_CODE', trim(Tools::getValue('client_code')));
                Configuration::updateValue('PARAM_CLIENT_USERNAME', trim(Tools::getValue('client_username')));
                Configuration::updateValue('PARAM_CLIENT_PASSWORD', trim(Tools::getValue('client_password')));
                Configuration::updateValue('PARAM_GUID', trim(Tools::getValue('guid')));
                Configuration::updateValue('PARAM_PAYMENTMETHOD', trim(Tools::getValue('paymentmethod')));
                Configuration::updateValue('PARAM_PAYMENTTYPE', implode(',', $types));
                Configuration::updateValue('PARAM_INSTALLMENT', trim(Tools::getValue('installment')));

                $this->context->smarty->assign('Param_save_success', true);
                Logger::addLog('Param configuration updated', 1, null);
            } else {
                $this->context->smarty->assign('Param_save_fail', true);
                $this->context->smarty->assign('Param_errors', $post_errors);
            }
        }
    }

    public function hookHeader()
    {
        $this->context->controller->addCSS(($this->_path).'views/css/front.css', 'all');
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }
    }

    public function hookPaymentOptions($params)
    {        
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return false;
        }
        if (!$this->active) {
            return array();
        }
        
        $sandbox = Configuration::get('PARAM_SANDBOX');
        $clientCode = Configuration::get('PARAM_CLIENT_CODE');
        $clientUsername = Configuration::get('PARAM_CLIENT_USERNAME');
        $clientPassword = Configuration::get('PARAM_CLIENT_PASSWORD');
        $guid = Configuration::get('PARAM_GUID');
        $paymentmethod = Configuration::get('PARAM_PAYMENTMETHOD');
        $installment = Configuration::get('PARAM_INSTALLMENT');
        $paymenttype = explode(',', Configuration::get('PARAM_PAYMENTTYPE'));
        if (count($paymenttype) == 0) {
            $paymenttype = array('visa', 'mastercard');
        }

        if (empty($clientCode) || empty($clientUsername) || empty($clientPassword) || empty($guid)) {
            return;
        }

        $is_failed = Tools::getValue('paramerror');

        /* Load objects */
        $address = new Address((int)$params['cart']->id_address_invoice);
        $shipping_address = new Address((int)$params['cart']->id_address_delivery);
        $customer = new Customer((int)$params['cart']->id_customer);

        $total_amount = number_format($params['cart']->getOrderTotal(), 2, '.', '') * 100;
        $redirect_url = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http')
            .'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/param.php';

        include_once(_PS_MODULE_DIR_.'/parampos/lib/Param/ParamAPI.php');


        // Call ParamAPI
        $param_params = array();
        if ($sandbox) {
            $param_params['sandbox'] = true;
        }
        $smarty = $this->context->smarty;

        $smarty->assign(array(
            'gateway_url' => $redirect_url.'?AccessCode=999&cart='.$params['cart']->id,
            'callback' => $redirect_url.'?AccessCode=999&cart='.$params['cart']->id,
            'AccessCode' => 999,
            'payment_type' => $paymenttype,
            'isFailed' => $is_failed,
            'installment' => $installment,
            'InstallmentUrl' => $redirect_url.'?getInstallment=1&cart='.$params['cart']->id,
            'module_dir' => _PS_MODULE_DIR_
        ));

        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $paymentOption->setCallToActionText($this->l('Pay by Card with Param POS'))
            ->setForm($this->context->smarty->fetch('module:parampos/views/templates/front/payment_form.tpl'))
            ->setAdditionalInformation(
                $this->context->smarty->fetch('module:parampos/views/templates/front/payment_infos.tpl')
            );
        return array($paymentOption);
    }

    public function hookPaymentReturn()
    {
        if (!$this->active) {
            return null;
        }
        return $this->context->smarty->fetch($this->local_path.'/views/templates/hook/confirmation.tpl');
    }

    public function getAccessCodeResult()
    {
        $sandbox = Configuration::get('PARAM_SANDBOX');
        $clientCode = Configuration::get('PARAM_CLIENT_CODE');
        $clientUsername = Configuration::get('PARAM_CLIENT_USERNAME');
        $clientPassword = Configuration::get('PARAM_CLIENT_PASSWORD');
        $guid = Configuration::get('PARAM_GUID');
        include_once(_PS_MODULE_DIR_.'/parampos/lib/Param/ParamAPI.php');

        if(Tools::getValue('getInstallment')) {
            $id_cart = (int)Tools::getValue('amp;cart');
            if (_PS_VERSION_ >= 1.5) {
                Context::getContext()->cart = new Cart($id_cart);
            }
            $cart = Context::getContext()->cart;
            
            if (!Validate::isLoadedObject($cart)) {
                die('An unrecoverable error occured with the cart ');
            }

            $ccNumber = Tools::getValue('ccnumber');
			$ccNumber = str_replace(' ', '', $ccNumber);
			$result = new stdClass();
			$result->G = new stdClass();
			$result->G->CLIENT_CODE = $clientCode;
			$result->G->CLIENT_USERNAME = $clientUsername;
			$result->G->CLIENT_PASSWORD = $clientPassword;
			$result->BIN = $ccNumber;

            $param_params = array();
            if ($sandbox) {
                $param_params['sandbox'] = true;
            }
            
            $service = new ParamAPI($clientCode, $clientUsername, $clientPassword, $guid, $param_params);
			$modelPayment = $service->getInstallments($result, $cart);
            header('Content-type: application/json; charset=utf-8');
			echo $modelPayment;
            die();
        }

        if(Tools::getValue('AccessCode')) 
        {
            $redirect_url = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http')
            .'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/param.php';
            $ref_url = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http')
            .'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?controller=order';
            
            if (_PS_VERSION_ >= 1.5) {
                Context::getContext()->cart = new Cart((int)Tools::getValue('cart'));
            }
            $cart = Context::getContext()->cart;
            $currency = new Currency((int)$cart->id_currency);
           
            $amount = number_format($cart->getOrderTotal(), 2, ',', '');
            
            $result = new stdClass();
            $result->SanalPOS_ID = '';
            $result->G = new stdClass();
            $result->G->CLIENT_CODE = $clientCode;
            $result->G->CLIENT_USERNAME = $clientUsername;
            $result->G->CLIENT_PASSWORD = $clientPassword;
            $result->GUID = $guid;
            $result->KK_Sahibi = Tools::getValue('PARAM_CARDNAME');
            $result->KK_No = Tools::getValue('PARAM_CARDNUMBER');
            $result->KK_CVC = Tools::getValue('PARAM_CARDCVN');
            $result->KK_SK_Ay = Tools::getValue('PARAM_CARDEXPIRYMONTH');
            $result->KK_SK_Yil = Tools::getValue('PARAM_CARDEXPIRYYEAR');
            $result->KK_Sahibi_GSM = '';
            $result->Hata_URL = $redirect_url;
            $result->Basarili_URL = $redirect_url;
            $result->Siparis_ID = Tools::getValue('cart') . rand(0, 9999);
            $result->Siparis_Aciklama = date("d-m-Y H:i:s") . " Tarihindeki Ödeme";
            if(Tools::getValue('PARAM_INSTALLMENT') &&  count($installment = explode('|', Tools::getValue('PARAM_INSTALLMENT'))) == 3)
            {
                $result->Taksit = $installment[0];
                $rate = $installment[1];
                $amount = number_format((1 + ($rate / 100)) *  $amount, 2, ',', '');
                $fee = (float) ($rate / 100) * $amount;
                $message = 'Takist: ' . $installment[0]. "\n";
                $message .= 'Komisyon Oranı: %' . $installment[1] . "\n";
                $message .= 'Komisyon Tutarı: ' . number_format(round($fee, 2), 2, ',', '') . $currency->iso_code . "\n";
                $message .= 'Tahsil Edilen Toplam Tutar: ' 	. number_format($amount + $fee, 2, ',', '') . $currency->iso_code ."\n";
                $result->Data2 = $message;
            } else 
            {
                $result->Taksit = '1';
                $result->Data2 = '';
            }

            $result->Islem_Tutar = $amount;
            $result->Toplam_Tutar = $amount;
            $result->Islem_Hash = '';
            $result->Islem_Guvenlik_Tip = '3D';
            $result->Islem_ID = str_replace(".","",microtime(true)).rand(000,999);
            $result->IPAdr = $_SERVER['REMOTE_ADDR'];
            $result->Ref_URL = $ref_url;
            $result->Data1 = base64_encode(json_encode(array(
                'Last4Digits' => substr(str_replace(' ', '', Tools::getValue('PARAM_CARDNUMBER')), -4, 4),
                'ExpiryDate' => Tools::getValue('PARAM_CARDEXPIRYMONTH') . '/' . Tools::getValue('PARAM_CARDEXPIRYYEAR')
            )));
            $result->Data3 = Tools::getValue('cart');
            $result->Data4 = '';
            $result->Data5 = '';
            $result->Data6 = '';
            $result->Data7 = '';
            $result->Data8 = '';
            $result->Data9 = '';
            $result->Data10 = '';
            //Dim Islem_Guvenlik_Str$ = CLIENT_CODE & GUID & Taksit & Islem_Tutar & Toplam_Tutar & Siparis_ID & Hata_URL & Basarili_URL
            $Islem_Guvenlik_Str = $result->G->CLIENT_CODE . $result->GUID . $result->Taksit . $result->Islem_Tutar . $result->Toplam_Tutar . $result->Siparis_ID . $result->Hata_URL . $result->Basarili_URL;
        
            // Call ParamAPI
            $param_params = array();
            if ($sandbox) {
                $param_params['sandbox'] = true;
            }
            $service = new ParamAPI($clientCode, $clientUsername, $clientPassword, $guid, $param_params);
            $response = $service->getPaymentRequest($result, $Islem_Guvenlik_Str);
        } 
        elseif (Tools::getValue('TURKPOS_RETVAL_Sonuc')) 
		{
            if (Tools::getValue('TURKPOS_RETVAL_Sonuc') < 0) 
			{
                $checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ?
                'order-opc' : 'order';

                $url = _PS_VERSION_ >= '1.5' ?
                    'index.php?controller='.$checkout_type.'&' : $checkout_type.'.php?';

                $url .= 'step=3&cgv=1&paramerror=1&message='.Tools::getValue('TURKPOS_RETVAL_Sonuc_Str');
                
                Logger::addLog(Tools::getValue('TURKPOS_RETVAL_Sonuc_Str'));
                Tools::redirect($url);
			} 
            else 
			{
                if(Tools::getValue('TURKPOS_RETVAL_Sonuc') == 1) 
				{
					$extraData = explode('|', Tools::getValue('TURKPOS_RETVAL_Ext_Data'));
                    $id_cart = (int)$extraData[2];
                    
                    if (_PS_VERSION_ >= 1.5) {
                        Context::getContext()->cart = new Cart($id_cart);
                    }
                    $cart = Context::getContext()->cart;
            
                    if (!Validate::isLoadedObject($cart)) {
                        die('An unrecoverable error occured with the cart ');
                    }
            
                    $customer = new Customer((int)$cart->id_customer);
                    $amount = number_format($cart->getOrderTotal(), 2, ',', '');
					$transcactionId = Tools::getValue('TURKPOS_RETVAL_Islem_ID');
					$docId = Tools::getValue('TURKPOS_RETVAL_Dekont_ID');
					$responseMessage = Tools::getValue('TURKPOS_RETVAL_Sonuc_Str');
                    $extra_vars = array();

                    if (!Validate::isLoadedObject($customer)) {
                        Logger::addLog('Issue loading customer');
                        die('An unrecoverable error occured while retrieving your data');
                    }
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
                    
                    $extra_vars['transaction_id'] = $transcactionId;
                    $extra_vars['amount'] = $amount;
                    $extra_vars['transaction_id'] = $transcactionId;
                    if(isset($additionalData))
                    {
                        $extra_vars['cardNumber'] = $additionalData['Last4Digits'];
                        $extra_vars['cardExpiration'] = $additionalData['ExpiryDate'];
                    }
                    
                    $order_total = number_format($cart->getOrderTotal(), 2, '.', '');
                    
                    $this->validateOrder(
                        $cart->id,
                        Configuration::get('PS_OS_PAYMENT'),
                        $order_total,
                        $this->displayName,
                        $this->l('Transaction ID: ').$transcactionId,
                        $extra_vars,
                        null,
                        false,
                        $customer->secure_key
                    );
            
                    $confirmurl = 'index.php?controller=order-confirmation&';
            
                    if (_PS_VERSION_ < '1.5') {
                        $confirmurl = 'order-confirmation.php?';
                    }
                    Tools::redirect(
                        $confirmurl.'id_module='.(int)$this->id.'&id_cart='.
                        (int)$cart->id.'&key='.$customer->secure_key
                    );
                }
            }
        }
    }
}
