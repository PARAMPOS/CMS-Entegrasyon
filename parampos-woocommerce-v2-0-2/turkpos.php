<?php
    /* 
     * Plugin Name: Param POS Payment Gateway
     * Plugin URI: https://param.com.tr
     * Description: Take credit card payments on your store.
     * Author: Param POS
     * Author URI: https://param.com.tr
     * Version: 2.0.3
     * Update Time: 07 Kasım 2024
     */
    ini_set("default_socket_timeout", 6000);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_samesite', 'None');


    global $wpdb;

    if (!defined('_DB_PREFIX_')) {
        define("_DB_PREFIX_", $wpdb->prefix);
    }

    include(plugin_dir_path(__FILE__) . '/setup/install.php');
    include(plugin_dir_path(__FILE__) . '/autoload.php');

    /* Install Function */
    register_activation_hook(__FILE__, 'param_activate');

    /*
     * This action hook registers our PHP class as a WooCommerce payment gateway
     */
    add_filter('woocommerce_payment_gateways', 'param_add_gateway_class');
    function param_add_gateway_class($gateways)
    {
        $gateways[] = 'WC_Param_Gateway'; // your class name is here
        return $gateways;
    }


    add_action('wp', 'addErrorParamPos');

    function addErrorParamPos()
    {

        if (is_checkout()) {
            if (isset($_REQUEST['message'])) {
                wc_add_notice($_REQUEST['message'], 'error');
            }
        }
    }

    /*
     * The class itself, please note that it is inside plugins_loaded action hook
     */
    add_action('plugins_loaded', 'param_init_gateway_class');
    function param_init_gateway_class()
    {
        if (!class_exists('WC_Payment_Gateway'))
            return;

        class WC_Param_Gateway extends WC_Payment_Gateway
        {
            private $testmode;
            private $client_code;
            private $client_username;
            private $client_password;
            private $guid;
            private $payment_url;
            private $version;
            private $installment;
            // private $pos_rates;
            // private $debug;

            /**
             * Class constructor, more about it in Step 3
             */
            public function __construct()
            {
                $this->id = 'param'; // payment gateway plugin ID
                $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
                $this->has_fields = true; // in case you need a custom credit card form
                $this->method_title = 'Param POS Payment Gateway - v2.0.2';
                $this->method_description = 'Param POS Payment Gateway'; // will be displayed on the options page

                // gateways can support subscriptions, refunds, saved payment methods,
                // but in this tutorial we begin with simple payments
                $this->supports = array(
                    'products'
                );

                // Method with all the options fields
                $this->init_form_fields();

                // Load the settings.
                $this->init_settings();
                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');
                $this->enabled = $this->get_option('enabled');
                $this->testmode = 'yes' === $this->get_option('testmode');
                $this->client_code = $this->get_option('client_code');
                $this->client_username = $this->get_option('client_username');
                $this->client_password = $this->get_option('client_password');
                $this->guid = $this->get_option('guid');
                $wsdlPath = WP_PLUGIN_DIR . '/turkpos/wsdl/ParamPOSApi.wsdl';
                if ($this->testmode) {
                    $this->payment_url = $this->get_option('test_payment_url');
                } else {
                    if (file_exists($wsdlPath)) {
                        $this->payment_url = $wsdlPath;
                    } else {
                        $this->payment_url = $this->get_option('payment_url');
                    }
                }

                // $this->version = $this->get_option('version');
                $this->installment = $this->get_option('installment');
                // $this->pos_rates = $this->get_option('pos_rates');
                // $this->debug = $this->get_option('debug');

                // This action hook saves the settings
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

                // We need custom JavaScript to obtain a token
                add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));

                // You can also register a webhook here
                //add_action('woocommerce_api_param', array( $this, 'webhook') );
                add_action('woocommerce_receipt_' . $this->id, array($this, 'payment_response'));
                add_action('woocommerce_api_parampos_complete_payment', array($this, 'complete'));

            }

            /**
             * Plugin options, we deal with it in Step 3 too
             */
            public function init_form_fields()
            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => 'Enable/Disable',
                        'label' => 'Param POS Aktif',
                        'type' => 'checkbox',
                        'description' => '',
                        'default' => 'no'
                    ),
                    'title' => array(
                        'title' => 'Param POS',
                        'type' => 'text',
                        'description' => 'This controls the title which the user sees during checkout.',
                        'default' => 'Kredi Kartı ile Öde',
                        'desc_tip' => true,
                    ),
                    'description' => array(
                        'title' => 'Param POS',
                        'type' => 'textarea',
                        'description' => 'This controls the description which the user sees during checkout.',
                        'default' => 'Pay with your credit card via our param payment gateway.',
                    ),
                    'testmode' => array(
                        'title' => 'Test mode',
                        'label' => 'Test Mode Aktif',
                        'type' => 'checkbox',
                        'description' => 'Place the payment gateway in test mode using test API keys.',
                        'default' => 'yes',
                        'desc_tip' => true,
                    ),
                    'client_code' => array(
                        'title' => 'Client Code',
                        'type' => 'text',
                        'default' => '10738'
                    ),
                    'client_username' => array(
                        'title' => 'Client Username',
                        'type' => 'text',
                        'default' => 'Test'
                    ),
                    'client_password' => array(
                        'title' => 'Client Password',
                        'type' => 'text',
                        'default' => 'Test',
                    ),
                    'guid' => array(
                        'title' => 'GUID',
                        'type' => 'text',
                        'default' => '0c13d406-873b-403b-9c09-a5766840d98c',
                    ),
                    'siparis_status' => array(
                        'title' => 'Ödeme Sonrası Sipariş Durumu',
                        'description' => 'Ödeme başarılı olduğunda ayarlanacak sipariş durumu.',
                        'type' => 'select',
                        'default' => 'wc-completed',
                        'options' => wc_get_order_statuses()
                    ),
                    // 'tek_cekim' => array(
                    //     'title' => 'Tek Çekim Komisyon Dahil',
                    //     'type' => 'checkbox',
                    //     'description' => 'Tek çekimde komisyonu firma öder',
                    //     'default' => 'no'
                    // ),

                    'installment' => array(
                        'title' => 'Taksit Seçimi',
                        'type' => 'checkbox',
                        'description' => 'Ödeme yaparken taksitleri göster.',
                        'default' => 'no'
                    ),
                    'installment_limit' => array(
                        'title' => 'X Lira Üzerine Taksiti Aktif Et',
                        'type' => 'text',
                        'description' => '100 lira üzerine taksitleri aktif et.',
                        'default' => '0'
                    ),
                    // 'pos_rates' => array(
                    //     'title' => 'Kullanıcak POS Oranları',
                    //     'description' => 'Kullanıcak POS Oranları',
                    //     'type' => 'select',
                    //     'default' => 'user',
                    //     'options' => array(
                    //         'user' => 'Kullanıcı Pos Oranları',
                    //         'merchant' => 'Firma Pos Oranları'
                    //     )
                    // ),
                    'pos_limit' => array(
                        'title' => 'Taksit Kısıtlaması',
                        'description' => 'Lütfen aktif etmek istediğiniz taksit sayısını seçiniz',
                        'type' => 'select',
                        'default' => 'user',
                        'options' => array(
                            '1' => '1',
                            '2' => '2',
                            '3' => '3',
                            '4' => '4',
                            '5' => '5',
                            '6' => '6',
                            '7' => '7',
                            '8' => '8',
                            '9' => '9',
                            '10' => '10',
                            '11' => '11',
                            '12' => '12',
                        )
                    ),
                    'payment_url' => array(
                        'type' => 'hidden',
                        'default' => 'https://posws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl',
                    ),
                    'test_payment_url' => array(
                        'type' => 'hidden',
                        'default' => 'https://test-dmz.param.com.tr/turkpos.ws/service_turkpos_test.asmx?wsdl',
                    ),
                );
            }

            /**
             * Undocumented function
             *
             * @param [type] $orderId
             * @return void
             */
            public function payment_response($orderId)
            {


                $helper = new Data();
                $result = $helper->validateTransactionResponse($orderId, $_POST);
                if ($_REQUEST['message']) {

                    wc_print_notice($_REQUEST['message'], 'error');
                    return wp_redirect(wc_get_checkout_url());
                }
            }

            /**
             * You will need it if you want your custom credit card form, Step 4 is about it
             */


            public function payment_fields()
            {

                if (get_locale() == 'tr_TR') {
                    $lang = [
                        'name' => 'Kart Sahibi Adı Soyadı',
                        'cart_no' => 'Kredi Kartı Numarası',
                        'cart_date' => 'AY/YIL',
                        'cvv' => 'CVV',
                        'installment' => 'Taksit Seçimi',
                        'choise' => '-- Lütfen Seçiniz --',
                        'cart_name' => 'Ad Soyad',
                        'all_installment' => 'Tüm taksit oranları ve seçeneklerini görüntülemek için tıklayınız',
                        'last' => 'Son kullanma tarihi',
                        'cvv_last' => 'CVV numarası',
                        'error' => 'eksik'
                    ];
                } else {
                    $lang = [
                        'name' => 'Name Surname',
                        'cart_no' => 'Credit Cart Number',
                        'cart_date' => 'MM/YY',
                        'cvv' => 'CVV',
                        'installment' => 'Instalment',
                        'choise' => '-- Please Choise --',
                        'cart_name' => 'Name Surname',
                        'all_installment' => 'Click to view all installment rates and options',
                        'last' => 'Expiration date',
                        'cvv_last' => 'CVV Number',
                        'error' => 'missing'
                    ];
                }

                // ok, let's display some description before the payment form
                if ($this->description) {
                    // you can instructions for test mode, I mean test card numbers etc.
                    if ($this->testmode) {
                        $this->description .= ' TEST MODE ENABLED. In test mode, you can use the card numbers listed in <a href="https://dev.param.com.tr/tr" target="_blank" rel="noopener noreferrer">documentation</a>.';
                        $this->description = trim($this->description);
                    }
                    // display the description with <p> tags etc.
                    echo wpautop(wp_kses_post($this->description));
                }

                // Show/Hide installment table
                if ($this->installment == 'yes') {
                    $installmentHtml = '<div class="clearfix"></div>
                <p class="form-row">
                    <a href="#popup-content" id="inst-link">' . $lang['all_installment'] . '</a>
                    <div id="popup-content" class="white-popup mfp-hide"></div>
                </p>';
                } else {
                    $installmentHtml = '';
                }


                // I will echo() the form, but you can close PHP tags and print it directly in HTML
                echo '<div id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

                // Add this action hook if you want your custom payment gateway to support it
                do_action('woocommerce_credit_card_form_start', $this->id);

                // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
                echo '<div class="card-wrapper"></div>

            
            
            <p class="form-row form-row-wide" id="cc_name_field">
                <label for="cc_name" class="">' . $lang['name'] . '&nbsp;<abbr class="required" title="required">*</abbr></label>
                <span class="woocommerce-input-wrapper">
                    <input type="text" class="input-text " name="cc_name" id="ccpp_creditcard_name_on_card" placeholder="' . $lang['name'] . '" value="" autocomplate="off">
                </span>
            </p>

            <p class="form-row form-row-wide" id="cc_number_field">
                <label for="cc_number" class="">' . $lang['cart_no'] . '&nbsp;<abbr class="required" title="required">*</abbr></label>
                <span class="woocommerce-input-wrapper">
                    <input type="tel" class="input-text" name="cc_number" id="ccpp_creditcard_cc_number" placeholder="' . $lang['cart_no'] . '" value="" autocomplete="off">
                </span>
            </p>

            <p class="form-row form-row-first" id="cc_expiry_field">
                <label for="cc_expiry" class="">' . $lang['cart_date'] . '&nbsp;<abbr class="required" title="required">*</abbr></label>
                <span class="woocommerce-input-wrapper">
                    <input type="text" class="input-text valid" name="cc_expiry" id="ccpp_creditcard_expiration" placeholder="' . $lang['cart_date'] . '" value="" autocomplete="off">
                </span>
            </p>

            <p class="form-row form-row-last " id="cc_cvv_field">
                <label for="cc_cvv" class="">' . $lang['cvv'] . '&nbsp;<abbr class="required" title="required">*</abbr></label>
                <span class="woocommerce-input-wrapper">
                    <input type="text" class="input-text " name="cc_cvv" id="ccpp_creditcard_cc_cid" placeholder="' . $lang['cvv'] . '" value="" autocomplete="off">
                </span>
            </p>
            
            <p class="form-row form-row-wide hidden" id="cc_installment_field">
                <label for="cc_installment" class="">' . $lang['installment'] . '&nbsp;<abbr class="required" title="required">*</abbr></label>
                <span class="woocommerce-input-wrapper">
                    <select name="cc_installment" class="form-control" id ="ccpp_creditcard_cc_installment">
                        <option value="">' . $lang['choise'] . '</option>
                    </select>
                </span>
                <!-- ' . $installmentHtml . ' -->
            </p>';
                echo "<script type='text/javascript'>var card = new Card({ form: '.woocommerce-checkout', container: '.card-wrapper',formatting: true,placeholders: {
                number: '•••• •••• •••• ••••',
                name: '" . $lang['name'] . "',
                expiry: '••/••',
                cvc: '•••'
            },masks: {
                cardNumber: '•' // optional - mask card number
            },formSelectors: {
                numberInput: 'input#ccpp_creditcard_cc_number', 
                expiryInput: 'input#ccpp_creditcard_expiration', 
                cvcInput: 'input#ccpp_creditcard_cc_cid',
                nameInput: 'input#ccpp_creditcard_name_on_card' 
            }, })</script>";
                do_action('woocommerce_credit_card_form_end', $this->id);
                echo '<div class="clearfix"></div></div>';
            }

            /*
             * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
             */
            public function payment_scripts()
            {
                // we need JavaScript to process a token only on cart/checkout pages, right?
                if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
                    return;
                }

                // if our payment gateway is disabled, we do not have to enqueue JS too
                if ('no' === $this->enabled) {
                    return;
                }

                // do not work with card detailes without SSL unless your website is in a test mode
                if (!$this->testmode && !is_ssl()) {
                    return;
                }

                wp_enqueue_script('validation_lib_js', plugins_url() . '/turkpos/views/js/jquery.validate.min.js');
                wp_enqueue_script('popup_lib_js', plugins_url() . '/turkpos/views/js/jquery.magnific-popup.min.js');

                wp_enqueue_script('jCard_js', plugins_url('/turkpos/views/js/card.js'));
                wp_enqueue_script('validation_js', plugins_url('/turkpos/views/js/validation.js'));

                wp_register_style('jCard_css', plugins_url() . '/turkpos/views/css/jquery.card.css');
                wp_register_style('param_css', plugins_url() . '/turkpos/views/css/param.css');
                wp_register_style('popup_lib_css', plugins_url() . '/turkpos/views/css/magnific-popup.css');

                wp_enqueue_style('param_css');
                wp_enqueue_style('popup_lib_css');

                wp_enqueue_script('validation_lib_js');
                wp_enqueue_script('popup_lib_js');

            }


            /*
              * Fields validation, more in Step 5
             */
            public function validate_fields()
            {

                if (get_locale() == 'tr_TR') {
                    $lang = [
                        'name' => 'Kart Sahibi Adı Soyadı',
                        'cart_no' => 'Kredi Kartı Numarası',
                        'cart_date' => 'MM/YY',
                        'cvv' => 'CVV',
                        'installment' => 'Taksit Seçimi',
                        'choise' => '-- Lütfen Seçiniz --',
                        'cart_name' => 'Ad Soyad',
                        'all_installment' => 'Tüm taksit oranları ve seçeneklerini görüntülemek için tıklayınız',
                        'last' => 'Son kullanma tarihi',
                        'cvv_last' => 'CVV numarası',
                        'error' => 'eksik'
                    ];
                } else {
                    $lang = [
                        'name' => 'Name Surname',
                        'cart_no' => 'Credit Cart Number',
                        'cart_date' => 'MM/YY',
                        'cvv' => 'CVV',
                        'installment' => 'Instalment',
                        'choise' => '-- Please Choise --',
                        'cart_name' => 'Name Surname',
                        'all_installment' => 'Click to view all installment rates and options',
                        'last' => 'Expiration date',
                        'cvv_last' => 'CVV Number',
                        'error' => 'missing'
                    ];
                }

                if (empty($_POST['cc_number'])) {
                    wc_add_notice('<strong>' . $lang['cart_no'] . '</strong>' . $lang['error'] . '', 'error');
                    return false;
                }
                if (empty($_POST['cc_name'])) {
                    wc_add_notice('<strong>' . $lang['name'] . '</strong>' . $lang['error'] . '', 'error');
                    return false;
                }
                if (empty($_POST['cc_expiry'])) {
                    wc_add_notice('<strong>' . $lang['last'] . '</strong> ' . $lang['error'] . '', 'error');
                    return false;
                }
                if (empty($_POST['cc_cvv'])) {
                    wc_add_notice('<strong>' . $lang['cvv_last'] . '</strong> ' . $lang['error'] . '', 'error');
                    return false;
                }
                return true;
            }

            /*
             * We're processing the payments here, everything about it is in Step 5
             */
            public function process_payment($order_id)
            {

                global $woocommerce;
                $order = new WC_Order($order_id);
                if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>=')) {
                    /* 2.1.0 */
                    $checkout_payment_url = $order->get_checkout_payment_url(true);
                } else {
                    /* 2.0.0 */
                    $checkout_payment_url = get_permalink(get_option('woocommerce_pay_page_id'));
                }



                $error_message = false;
                $transaction = Transaction::createTransaction();
                $paramHelper = new Data();
                $status = $order->get_status();
                $cur_name = get_woocommerce_currency();
                $currency = $paramHelper->getCurrency($cur_name);

                $transaction->orderId = $order_id;
                $transaction->testMode = $this->testmode;
                $transaction->serviceUrl = $this->payment_url;
                $transaction->gateway_params = new stdClass();
                $transaction->gateway_params->client_code = $this->client_code;
                $transaction->gateway_params->client_username = $this->client_username;
                $transaction->gateway_params->client_password = $this->client_password;
                $transaction->gateway_params->guid = $this->guid;
                $transaction->gateway_params->test_mode = $this->testmode;

                $pos = new InitPOS();
                $transaction = $pos->pay($transaction);
                $transaction->saveTransaction();


                if ($transaction->result <= 0) {
                    $error_message = $transaction->resultCode . ' ' . $transaction->resultMessage;
                    wc_add_notice($error_message, 'error');
                    return;
                }


                if ($transaction->tds and $transaction->redirectUrl) {

                    return array(
                        'result' => 'success',
                        'redirect' => $transaction->redirectUrl,
                    );
                }
            }


            public function complete()
            {
                $checkPaymentControl = $this->odemeSorgula($_REQUEST);
                if($checkPaymentControl["success"]){
                    $orderId = $_REQUEST['TURKPOS_RETVAL_Siparis_ID'];

                    /* Test işlemleri için InıtPos.php içerisinde orderId değerine rand ve time fonksiyonları uygulanıyor
                     * O alanda eklediğimiz rand ve time fonksiyonlarıyla gelen değerler bu alanda kaldırılıyor. Çakışmayı önlemek için.
                     * Example -> orderId = 41-rand(1,1000).time()
                    */
                    if ($this->testmode) {
                        $position = strpos($orderId, "-");
                        $result = substr($orderId, 0, $position);
                        $orderId = $result;
                    }

                    $order = wc_get_order($orderId);
                    $helper = new Data();
                    $result = $helper->validateTransactionResponse($orderId, $_POST);


                    $notes = wc_get_order_notes(["order_id" => $orderId, "type" => "admin"]);
                    $notes = array_column($notes, "content");
                    $notes = array_map(function ($d) {
                        return current(explode(" İşlem no:", $d));
                    }, $notes);

                        $tr = new Transaction();
                        $orderTransaction = $tr->getTransactionByOrderId($orderId);
                        $paramm = get_option("woocommerce_param_settings");



                        $order_statuse = $paramm["siparis_status"];
                        $order->update_status($order_statuse, 'Processing Param POS payment');
                        $order->add_order_note('Ödeme Param POS ile tamamlandı. İşlem no: #' . $orderTransaction->trId);
                        $order->payment_complete();
                        WC()->cart->empty_cart();

                        $thank_you_page_url = $order->get_checkout_order_received_url();
                        header("Location: " . $thank_you_page_url);
                        exit;

                } elseif ($checkPaymentControl["success"] === false && $checkPaymentControl["code"] === 400) {
                    return wp_redirect(wc_get_checkout_url() . "?message=" . $checkPaymentControl["code"]);
                } else {
                    return wp_redirect(wc_get_checkout_url() . "?message=" . $_REQUEST['TURKPOS_RETVAL_Sonuc_Str']);
                }
            }

            public function odemeSorgula()
            {


                /* Test işlemleri için InıtPos.php içerisinde orderId değerine rand ve time fonksiyonları uygulanıyor
                    * O alanda eklediğimiz rand ve time fonksiyonlarıyla gelen değerler bu alanda kaldırılıyor. Çakışmayı önlemek için.
                    * Example -> orderId = 41-rand(1,1000).time()
                */
                $requestOrderId = $_REQUEST['TURKPOS_RETVAL_Siparis_ID'];
                $paramm = get_option("woocommerce_param_settings");


                $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
                <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                <soap:Body>
                    <TP_Islem_Sorgulama4 xmlns="https://turkpos.com.tr/">
                    <G>
                        <CLIENT_CODE>' . $paramm['client_code'] . '</CLIENT_CODE>
                        <CLIENT_USERNAME>' . $paramm['client_username'] . '</CLIENT_USERNAME>
                        <CLIENT_PASSWORD>' . $paramm['client_password'] . '</CLIENT_PASSWORD>
                    </G>
                    <GUID>' . $_REQUEST['TURKPOS_RETVAL_GUID'] . '</GUID>
                    <Dekont_ID>' . $_REQUEST['TURKPOS_RETVAL_Dekont_ID'] . '</Dekont_ID>
                    <Siparis_ID></Siparis_ID>
                    <Islem_ID></Islem_ID>
                    </TP_Islem_Sorgulama4>
                </soap:Body>
                </soap:Envelope>';

                if($paramm['testmode']=="yes"){
                    $xml = $this->curlPost("https://test-dmz.param.com.tr/turkpos.ws/service_turkpos_test.asmx", $xmlRequest);
                } else {
                    $xml = $this->curlPost("https://posws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx", $xmlRequest);

                }
                $odeme_sonuc = $this->ara('<Odeme_Sonuc>', '</Odeme_Sonuc>', $xml)[0];
                $responseOrderId = $this->ara('<Siparis_ID>', '</Siparis_ID>', $xml)[0];

                /** Kullanıcıdan gelen Request'de bulunan orderId değeri ile param servislerinden dönen orderId birbirine eşit mi kontrolü yapılıyor. */
                if ($requestOrderId !== $responseOrderId) {
                    return [
                        "success" => false,
                        "code" => 400,
                        "message" => "Bad Request - Order Ids incorrect"
                    ];
                }

                if($odeme_sonuc==1){
                    return [
                        "success" => true,
                        "code" => 200,
                        "message" => "Successfly"
                    ];
                    return true;
                } else {
                    return [
                        "success" => false,
                        "code" => 404,
                        "message" => "Error"
                    ];
                }
            }

            public function ara($bas, $son, $yazi)
            {
                preg_match_all('@' . preg_quote($bas, '/') . '(.*?)' . preg_quote($son, '/') . '@s', $yazi, $m);
                return $m[1];
            }

            public function curlPost($url, $params)
            {
                $ch = curl_init();
                $headers = array(
                    "Content-Type: text/xml",
                    "Content-length: " . strlen($params),
                );
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

                $output = curl_exec($ch);
                curl_close($ch);
                return $output;
            }


        }
    }

    function custom_order_cancel_and_message()
    {
        if (isset($_GET["key"]) && isset($_GET["paramres"])) {
            $order_key = wc_clean($_GET['key']);
            $order_id = wc_get_order_id_by_order_key($order_key);
            $order = wc_get_order($order_id);

            if ($order) {
                // Eğer iptal edildiyse
                if ($_GET["paramres"] === "fail") {
                    // Siparişi iptal et
                    $order->update_status('cancelled');

                    // Ödeme başarısız mesajını ayarla
                    wc_add_notice('Ödeme başarısız oldu. Lütfen ödeme yönteminizi kontrol edin.', 'error');


                    // İptal sayfasına yönlendir
                    $checkout_url = wc_get_checkout_url(); // "checkout" sayfasının URL'sini alır
                    wp_safe_redirect($checkout_url);
                    exit;
                }
            }
        }
    }


    function thankyou_redirectx()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_GET["key"]) and isset($_GET["paramres"])) {
                $order_key = wc_clean($_GET['key']);
                $order_id = wc_get_order_id_by_order_key($order_key);
                $order = wc_get_order($order_id);

                if ($order) {
                    $thank_you_url = $order->get_checkout_order_received_url();
                    wp_safe_redirect($thank_you_url . "&paramres=" . $_GET["paramres"]);
                    exit;
                }
            }
        }
    }

    add_action('init', 'thankyou_redirectx');
    /**
     * Undocumented function
     *
     * @param [type] $orderId
     * @return void
     */
    function param_pos_order_details($orderId)
    {

        $tr = new Transaction();
        $orderTransaction = $tr->getTransactionByOrderId($orderId);
        if (!$transaction = $orderTransaction)
            return false;
        $ui = new OrderView();
        echo $ui->displayAdminOrder($transaction);
    }

    add_action('woocommerce_order_actions_end', 'param_pos_order_details');
    add_action('wp_footer', 'checkout_billing_email_js_ajax');
    function checkout_billing_email_js_ajax()
    {
        $paramGateway = new WC_Param_Gateway();
        // Only on Checkout
        global $woocommerce;
        $subtotal = (float)$woocommerce->cart->total;
        if (is_checkout() && !is_wc_endpoint_url() && $paramGateway->settings['installment'] !== 'no' && $paramGateway->settings['installment_limit'] <= $subtotal)  :
            ?>
            <script type="text/javascript">
                jQuery(function ($) {
                    if (typeof wc_checkout_params === 'undefined')
                        return false;

                    $(document.body).on("click", "a#inst-link", function (evt) {
                        evt.preventDefault();
                        $.ajax({
                            type: 'POST',
                            url: wc_checkout_params.ajax_url,
                            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                            enctype: 'multipart/form-data',
                            data: {
                                'action': 'ajax_order',
                                'show_installment': true,
                            },
                            beforeSend: function () {
                                $('#cc_installment_field').after(window.loader);
                            },
                            success: function (result) {
                                $('.custom-spinner').remove();
                                $('#popup-content').html(result);
                                $.magnificPopup.open({
                                    items: {
                                        src: '<div class="white-popup">' + $('#popup-content').html() + '</div>',
                                        type: 'inline'
                                    }
                                });
                            },
                            error: function (error) {

                            }
                        });
                    });
                });
            </script>
            <script type="text/javascript">
                jQuery(function ($) {
                    window.loader = '<div class="clearfix"></div><div class="spinner-border custom-spinner" role="status"><span class="sr-only">Yükleniyor...</span></div>';
                    if (typeof wc_checkout_params === 'undefined')
                        return false;
                    var requestOn = false;
                    $(document.body).on("keyup", "input#ccpp_creditcard_cc_number", function (evt) {
                        evt.preventDefault();
                        $('#cc_installment_field').addClass('hidden');
                        $('#ccpp_creditcard_cc_installment').find('option:not(:first)').remove();
                        var len = $(this).val().replace(/\s+/g, '').length;
                        if (len >= 6 && requestOn == false) {
                            $.ajax({
                                type: 'POST',
                                url: wc_checkout_params.ajax_url,
                                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                                enctype: 'multipart/form-data',
                                data: {
                                    'action': 'ajax_order',
                                    'fields': $('form.checkout').serializeArray(),
                                    'user_id': <?php echo get_current_user_id(); ?>,
                                },
                                beforeSend: function () {
                                    requestOn = true;
                                    $('#cc_installment_field').after(loader);
                                },
                                complete: function (response) {
                                    requestOn = false;
                                },
                                success: function (result) {
                                    $('#cc_installment_field').removeClass('hidden');
                                    $('.custom-spinner').remove();
                                    var data = jQuery.parseJSON(result);

                                    if (data.Sonuc == -2) {
                                        $('[for="cc_installment"]').html('<span style="color: red; font-weight: bold"> ' + data.Sonuc_Str + '</span>');
                                        $('[name="woocommerce_checkout_place_order"]').attr('disabled', true);
                                        $('#inst-link').remove();
                                        $('#ccpp_creditcard_cc_installment').remove();

                                    } else if (Object.keys(data).length > 0) {

                                        $.each(data, function (index, value) {
                                            if (index < 2) {
                                                $('#ccpp_creditcard_cc_installment').append($('<option>', {
                                                    value: index + '|' + value.rate + '|' + value.fee,
                                                    text: ' Tek Çekim - %' + value.rate + ' Komisyon - Genel Toplam ' + value.total_pay
                                                }));

                                            } else {


                                                $('#ccpp_creditcard_cc_installment').append($('<option>', {
                                                    value: index + '|' + value.rate + '|' + value.fee,
                                                    text: index + ' Taksit - %' + value.rate + ' Komisyon - Genel Toplam ' + value.total_pay
                                                }));
                                            }


                                        });
                                    } else {
                                        $('#cc_installment_field').addClass('hidden');
                                    }
                                },
                                error: function (error) {

                                }
                            });
                        } else {
                            $('#cc_installment_field').addClass('hidden');
                        }
                    });
                    $(document.body).on('input', 'input#ccpp_creditcard_expiration', function() {
                        // Sadece rakamları al
                        var input = $(this).val().replace(/\D/g, '');

                        // En fazla 4 karakter al
                        input = input.substring(0, 4);

                        // İlk iki karakter ayları, sonraki iki karakter yılı temsil eder
                        var month = input.substring(0, 2);
                        var year = input.substring(2);

                        // Ayları 01 ile 12 arasında tut
                        if (month.length === 2) {
                            month = (parseInt(month) < 1) ? '01' : (parseInt(month) > 12) ? '12' : month;
                        }

                        // Eğer tüm karakterler silinirse, boş bırak
                        var formattedInput = '';
                        if (month !== '' || year !== '') {
                            formattedInput = month + (year.length > 0 ? ' / ' + year : '');
                        }

                        // Input alanına formatlanmış değeri yaz
                        $(this).val(formattedInput);
                    });
                });
            </script>
        <?php
        endif;
    }

    add_action('wp_ajax_ajax_order', 'get_bank_installments');
    add_action('wp_ajax_nopriv_ajax_order', 'get_bank_installments');
    function get_bank_installments()
    {
        global $woocommerce;
        $cur_name = get_woocommerce_currency();
        $paramGateway = new WC_Param_Gateway();
        $CLIENT_CODE = $paramGateway->settings['client_code'];
        $CLIENT_USERNAME = $paramGateway->settings['client_username'];
        $CLIENT_PASSWORD = $paramGateway->settings['client_password'];
        $GUID = $paramGateway->settings['guid'];
        $MODE = $paramGateway->settings['testmode'] == "yes" ? "TEST" : "PROD";
        $wsdlPath = WP_PLUGIN_DIR . '/turkpos/wsdl/ParamPOSApi.wsdl';

        if ($paramGateway->settings['testmode'] == 'yes') {
            $serviceUrl = $paramGateway->settings['test_payment_url'];
        } else {
            if (file_exists($wsdlPath)) {
                $serviceUrl = $wsdlPath;
            } else {
                $serviceUrl = $paramGateway->settings['payment_url'];
            }
        }

        // installment info table
        if (isset($_POST['show_installment']) && !empty($_POST['show_installment'])) {
            // if ('user' === $paramGateway->settings['pos_rates']) {
            //     $cc = new InstallmentForUser($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
            // } else {
            //     $cc = new InstallmentForMerchant($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
            // }
            $cc = new InstallmentForUser($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);

            $response = $cc->send()->fetchInstallment();

            $html =
                '<table id="installment-table" class="table table-hover">
            <thead>
                <tr><th class="col-sm-1"></th><th>Banka</th>
                    <th>3 Taksit</th>
                    <th>6 Taksit</th>
                    <th>9 Taksit</th>
                    <th>12 Taksit</th>
                </tr>
            </thead>';
            $calcData = [];


            foreach ($response as $key => $obj):
                $installmentIndex = 12;
                for ($i = 1; $i <= $installmentIndex; $i++) {
                    $prerate = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $rate = $obj[0]["MO_$prerate"];
                    if (floatval($rate) < 0)
                        continue;
                    $subtotal = (float)$woocommerce->cart->total;
                    $amount = (float)(1 + ($rate / 100)) * $subtotal;
                    $fee = (float)($rate / 100) * $amount;
                    $calcData[$key][$prerate]['rate'] = $rate;
                    $calcData[$key][$prerate]['amount'] = number_format($amount, 2) . ' ' . $cur_name;
                    $calcData[$key][$prerate]['fee'] = $fee;
                }

                $banka_gorsel = isset($obj[0]['Kredi_Karti_Banka_Gorsel']) ? $obj[0]['Kredi_Karti_Banka_Gorsel'] : '';
                $kredi_karti_banka = isset($obj[0]['Kredi_Karti_Banka']) ? $obj[0]['Kredi_Karti_Banka'] : '';
                $sanal_pos_id = isset($obj[0]['SanalPOS_ID']) ?: '';


                if (isset($calcData[$key]['03']['rate'])) {


                    $html .= '
                <tr class="sanalPosID" rel="' . $sanal_pos_id . '">
                    <td class="col-sm-1"><img src="' . $banka_gorsel . '"></td>
                    <td>' . $kredi_karti_banka . '</td>
                    <td class="oranCol"><label>%' . floatval($calcData[$key]['03']['rate']) . '</label><div><span class="price">' . $calcData[$key]['03']['amount'] . '</span></div></td>
                    <td class="oranCol"><label>%' . floatval($calcData[$key]['06']['rate']) . '</label><div><span class="price">' . $calcData[$key]['06']['amount'] . '</span></div></td>
                    <td class="oranCol"><label>%' . floatval($calcData[$key]['09']['rate']) . '</label><div><span class="price">' . $calcData[$key]['09']['amount'] . '</span></div></td>
                    <td class="oranCol"><label>%' . floatval($calcData[$key]['12']['rate']) . '</label><div><span class="price">' . $calcData[$key]['12']['amount'] . '</span></div></td>
                </tr>';
                }
            endforeach;
            $html .= '<tbody></table>';
            echo $html;
            die();
        }

        // installment from BIN number
        if (isset($_POST['fields']) && !empty($_POST['fields'])) {
            $data = [];
            foreach ($_POST['fields'] as $values) {
                if ($values['name'] == 'cc_number' && !empty($values['value'])) {
                    $data[$values['name']] = $values['value'];
                }
            }
            if (count($data)) {
                $data['cc_number'] = str_replace(' ', '', $data['cc_number']);
                $bin = new Bin($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
                $bin_response = $bin->send($data['cc_number'])->fetchBIN();

                if (isset($bin_response["posId"])) {
                    $posId = $bin_response["posId"];

                    // if ('user' === $paramGateway->settings['pos_rates']) {
                    //     $cc = new InstallmentForUser($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
                    // } else {
                    //     $cc = new InstallmentForMerchant($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
                    // }

                    $cc = new InstallmentForUser($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
                    $response = $cc->send()->fetchInstallment();

                    $installment = [];

                    $paramGateway = new WC_Param_Gateway();
                    // Only on Checkout
                    global $woocommerce;
                    $tek_cekim = $paramGateway->settings['installment'];

                    if (isset($response['Sonuc'])) {
                        wp_die($response['Sonuc_Str']);
                    }

                    foreach ($response as $key => $resp) {
                        if ($resp[0]["SanalPOS_ID"] == $posId) {
                            //Eğer dönen "cardType" Debit Card'a eşitse sadece ilk taksidi dönder. Debit Card değilse ayarlarda belirtilen pos_limit getir.
                            $installmentIndex = ($bin_response['cardType'] === "Debit Kart" || $bin_response['cardType'] === "Debit Card" || $bin_response['cardType'] === "Prepaid Card") ? 1 : $paramGateway->settings['pos_limit'];
//                          $installmentIndex = $paramGateway->settings['pos_limit'];

                            for ($i = 1; $i <= $installmentIndex; $i++) {
                                $prerate = str_pad($i, 2, '0', STR_PAD_LEFT);
                                $rate = $resp[0]["MO_$prerate"];
                                if (floatval($rate) < 0)
                                    continue;

                                // if ($paramGateway->settings['tek_cekim'] == 'yes' & $i == 1) {
                                //     $rate = 0;
                                //     $subtotal = (float)$woocommerce->cart->total;
                                //     $amount = (float)(1 + ($rate / 100)) * $subtotal;
                                //     $fee = (float)($rate / 100) * $subtotal;
                                //     $installment[$i]['month'] = $prerate;
                                //     $installment[$i]['rate'] = $rate;
                                //     $installment[$i]['total_pay'] = number_format($amount, 2) . ' ' . $cur_name;
                                //     $installment[$i]['fee'] = 0;
                                // } else {
                                    $subtotal = (float)$woocommerce->cart->total;
                                    $amount = (float)(1 + ($rate / 100)) * $subtotal;
                                    $fee = (float)($rate / 100) * $subtotal;
                                    $installment[$i]['month'] = $prerate;
                                    $installment[$i]['rate'] = number_format($resp[0]["MO_$prerate"], 2);
                                    $installment[$i]['total_pay'] = number_format($amount, 2) . ' ' . $cur_name;
                                    $installment[$i]['fee'] = number_format($fee, 2);
                                // }
                            }
                        }
                    }
                    echo json_encode($installment, true);
                } else {
                    echo json_encode($bin_response, true);
                }

            } else {
                echo json_encode([], true);
            }
        }
        die();
    }

