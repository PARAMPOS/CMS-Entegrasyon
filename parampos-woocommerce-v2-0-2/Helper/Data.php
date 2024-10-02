<?php

class Data
{	
    /**
     * get ip address
     *
     * @return void
     */
    public static function getClientIp()
	{
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
		} else {
			$headers = $_SERVER;
		}

		if (array_key_exists('X-Forwarded-For', $headers)) {
			$_SERVER['HTTP_X_FORWARDED_FOR'] = $headers['X-Forwarded-For'];
		}

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && (!isset($_SERVER['REMOTE_ADDR']) || preg_match('/^127\..*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^172\.16.*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^192\.168\.*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^10\..*/i', trim($_SERVER['REMOTE_ADDR'])))) {
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
				$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				return $ips[0];
			} else {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

    /**
     *
     * @param [type] $currency
     * @return void
     */
    public static function getCurrency($currency)
	{
		$currencies = array(
			'EUR' => array('Euro', '978'),
			'TRY' => array('Türk Lirası', '949'),
			'USD' => array('US Dollars', '840'),
		);

        if (isset($currencies[$currency])) {
            $cur = $currencies[$currency];
        }
        
        return (object) array(
            'name' => 'Türk Lirası',
            'iso_code' => "TRY",
            'iso_code_num' => 949,
            'sign' => get_woocommerce_currency_symbol($currency),
		);
	}

    /**
     *
     * @return void
     */
    public function getDiscounts()
	{
		$cart = New Cart($this->id_cart);
		return $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
	}

    /**
     *
     * @param [type] $orderId
     * @param [type] $data
     * @return void
     */
    public function validateTransactionResponse($orderId, $data)
	{
        $message = [
			'message' => '',
			'error' => false
		];

		if(!isset($data['TURKPOS_RETVAL_Sonuc']) || (isset($data['TURKPOS_RETVAL_Sonuc']) && intval($data['TURKPOS_RETVAL_Sonuc']) < 1)) {
			$message['error'] = true;
			$message['message'] = isset($data['TURKPOS_RETVAL_Sonuc_Str'])?$data['TURKPOS_RETVAL_Sonuc_Str']:"";
            return $message;
		}

		if(intval($data['TURKPOS_RETVAL_Sonuc']) < 1) {
			$message['error'] = true;
			$message['message'] = 'Hatalı Sipariş';
		}
		return $message;
	}
    

    /**
     * Undocumented function
     *
     * @param [type] $key
     * @return void
     */
    public static function get($key)
	{
		return get_option($key);
	}

    /**
     *
     * @param [type] $key
     * @param [type] $value
     * @return void
     */
	public static function set($key, $value)
	{
		return update_option($key, $value);
	}

    /**
     *
     * @param [type] $key
     * @param boolean $defaultValue
     * @return void
     */
    public static function getParam($key, $defaultValue = null)
	{
		$value = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));
        $escaper = new Escaper();
        $value = $escaper->escapeHtml($value);
		return $value;
	}

    /**
     *
     * @param [type] $ccno
     * @return void
     */
    public static function maskCcNo($ccNumber)
	{
		return substr((string) $ccNumber, 0, 6) . 'XXXXXXXX' . substr((string) $ccNumber, -2);
	}
}
