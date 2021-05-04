<?php

class Etictools
{
	/* Return and Assign Message */

	public static function rwm($message, $return = false, $type = 'fail')
	{
		if ($type == 'fail' OR $type == 'error')
			$type = 'danger';
		EticConfig::$messages [] = array('message' => $message, 'type' => $type);
		return $return;
	}

	public static function getValue($key, $default_value = false)
	{
		if (!isset($key) || empty($key) || !is_string($key))
			return false;

		$ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default_value));

		if (is_string($ret))
			return stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret))));

		return $ret;
	}

	public static function getCustomerName($id_customer)
	{
		if ($customer = New CustomerCore((int) $id_customer) AND $customer->id) {
			return $customer->firstname . ' ' . $customer->lastname;
		}
		return "-";
	}

	public static function escape($string, $html_ok = false, $bq_sql = false)
	{
		$string = addslashes(stripslashes($string));

		if (!is_numeric($string)) {

			if (!$html_ok) {
				$string = strip_tags($string);
			}

			if ($bq_sql === true) {
				$string = str_replace('`', '\`', $string);
			}
		}
		return $string;
	}

	public static function fixTrUnigateway($q)
	{
		$q = str_replace("Ä°", "İ", $q);
		$q = str_replace("Ã§", "ç", $q);
		$q = str_replace("Å", "ş", $q);
		return $q;
	}

	public static function encodetxt($txt)
	{
		return addslashes($txt);
		return base64_engateway($txt);
	}

	public static function decodetxt($txt)
	{
		return $txt;
		return base64_degateway($txt);
	}

	public function xml2array($xmlObject, $out = array())
	{
		foreach ((array) $xmlObject as $index => $node)
			$out[$index] = ( is_object($node) || is_array($node) ) ? $this->xml2array($node) : $node;

		return $out;
	}

	public static function generateKey($id, $dyn = true)
	{
		return md5($_SERVER['REMOTE_ADDR'] . $id . ($dyn ? date("md") : '') . $_SERVER['HTTP_HOST']);
	}

	public static function curlPostExt($data, $url, $json = false)
	{
		$ch = curl_init(); // initialize curl handle
		curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
		if ($json)
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 4s
		curl_setopt($ch, CURLOPT_POST, 1); // set POST method
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // add POST fields
		if ($result = curl_exec($ch)) { // run the whole process
			curl_close($ch);
			return $result;
		}
		return false;
	}

	public static function curlGet($url = null)
	{
		$ch = curl_init(); // initialize curl handle
		curl_setopt($ch, CURLOPT_URL, $url);   // set url to gatewayt to
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 4s
		if ($result = curl_exec($ch)) {  // run the whole process
			curl_close($ch);
			return $result;
		}
		return curl_error($ch);
	}

	public static function getTagValue($tag, $str)
	{
		$stag = "<" . $tag . ">";
		$etag = "</" . $tag . ">";
		$pop1 = explode($stag, $str);
		$pop2 = explode($etag, $pop1[1]);
		return $pop2[0];
	}

	public static function fixEncoding($in_str)
	{
		$from = array("/ Ğ/", "/Ü/ ", "/ Ş

            /", "/İ/", "/Ö/", "/Ç/", "/ğ/", "/ü/", "/ş/", "/ı/", "/ö/", "/ç/", '^');
		$to = array("G", "U", "S", "I", "O", "C", "g", "u", "s", "i", "o", "c", '');
		$in_str = str_replace($from, $to, $in_str);
		$cur_encoding = mb_detect_encoding($in_str);
		if ($cur_encoding == "UTF-8" && mb_check_encoding($in_str, "UTF-8"))
			return $in_str;
		return utf8_engateway($in_str);
	}

	public function simpleXMLToArray($xml, $flattenValues = true, $flattenAttributes = true, $flattenChildren = true, $valueKey = '@value', $attributesKey = '@attributes', $childrenKey = '@children')
	{

		$return = array();
		if (!($xml instanceof SimpleXMLElement)) {
			return $return;
		}
		$name = $xml->getName();
		$_value = trim((string) $xml);
		if (strlen($_value) == 0) {
			$_value = null;
		};

		if ($_value !== null) {
			if (!$flattenValues) {
				$return[$valueKey] = $_value;
			} else {
				$return = $_value;
			}
		}

		$children = array();
		$first = true;
		foreach ($xml->children() as $elementName => $child) {
			$value = $this->simpleXMLToArray($child, $flattenValues, $flattenAttributes, $flattenChildren, $valueKey, $attributesKey, $childrenKey);
			if (isset($children[$elementName])) {
				if ($first) {


					$temp = $children[$elementName];
					unset($children[$elementName]);
					$children[$elementName][] = $temp;
					$first = false;
				}
				$children[$elementName][] = $value;
			} else {
				$children[$elementName] = $value;
			}
		}
		if (count($children) > 0) {
			if (!$flattenChildren) {
				$return[$childrenKey] = $children;
			} else {
				$return = array_merge($return, $children);
			}
		}

		$attributes = array();
		foreach ($xml->attributes() as $name => $value) {
			$attributes[$name] = trim($value);
		}
		if (count($attributes) > 0) {
			if (!$flattenAttributes) {
				$return[$attributesKey] = $attributes;
			} else {
				$return = array_merge($return, $attributes);
			}
		}

		return (object) $return;
	}

	public static function displayPrice($price)
	{
		return Tools::displayPrice($price);
	}

	public static function getIp()
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

	public static function maskCcNo($ccno)
	{
		return substr((string) $ccno, 0, 6) . 'XXXXXXXX' . substr((string) $ccno, -2);
	}

	public static function isMobile($phone)
	{
		$numbers = Etictools::formatMobile($phone);
		return is_numeric($numbers) && substr($numbers, 2, 1) == 5;
	}

	public static function formatMobile($phone)
	{
		return '90' . substr(preg_replace('/[^0-9]/', '', $phone), -10);
		/*
		  Test $phones = array(
		  );
		  foreach($phones as $phone){
		  echo "<br/>".Etictools::formatMobile($phone);
		  var_dump(Etictools::isMobile($phone));
		  }
		 */
	}

	public static function removeBOM($data)
	{
		if (0 === strpos(bin2hex($data), 'efbbbf')) {
			return substr($data, 3);
		}
		return $data;
	}

	public static function recursive_array_search($needle, $haystack)
	{
		foreach ($haystack as $key => $value) {
			if ($needle === $value) {
				return array($key);
			} else if (is_array($value) && $subkey = Etictools::recursive_array_search($needle, $value)) {
				array_unshift($subkey, $key);
				return $subkey;
			}
		}
	}

	public static function getCurrency($cur, $by = 'iso_name')
	{
		$currencies = array(
			'AFA' => array('Afghan Afghani', '971'),
			'AWG' => array('Aruban Florin', '533'),
			'AUD' => array('Australian Dollars', '036'),
			'ARS' => array('Argentine Pes', '032'),
			'AZN' => array('Azerbaijanian Manat', '944'),
			'BSD' => array('Bahamian Dollar', '044'),
			'BDT' => array('Bangladeshi Taka', '050'),
			'BBD' => array('Barbados Dollar', '052'),
			'BYR' => array('Belarussian Rouble', '974'),
			'BOB' => array('Bolivian Boliviano', '068'),
			'BRL' => array('Brazilian Real', '986'),
			'GBP' => array('British Pounds Sterling', '826'),
			'BGN' => array('Bulgarian Lev', '975'),
			'KHR' => array('Cambodia Riel', '116'),
			'CAD' => array('Canadian Dollars', '124'),
			'KYD' => array('Cayman Islands Dollar', '136'),
			'CLP' => array('Chilean Peso', '152'),
			'CNY' => array('Chinese Renminbi Yuan', '156'),
			'COP' => array('Colombian Peso', '170'),
			'CRC' => array('Costa Rican Colon', '188'),
			'HRK' => array('Croatia Kuna', '191'),
			'CPY' => array('Cypriot Pounds', '196'),
			'CZK' => array('Czech Koruna', '203'),
			'DKK' => array('Danish Krone', '208'),
			'DOP' => array('Dominican Republic Peso', '214'),
			'XCD' => array('East Caribbean Dollar', '951'),
			'EGP' => array('Egyptian Pound', '818'),
			'ERN' => array('Eritrean Nakfa', '232'),
			'EEK' => array('Estonia Kroon', '233'),
			'EUR' => array('Euro', '978'),
			'GEL' => array('Georgian Lari', '981'),
			'GHC' => array('Ghana Cedi', '288'),
			'GIP' => array('Gibraltar Pound', '292'),
			'GTQ' => array('Guatemala Quetzal', '320'),
			'HNL' => array('Honduras Lempira', '340'),
			'HKD' => array('Hong Kong Dollars', '344'),
			'HUF' => array('Hungary Forint', '348'),
			'ISK' => array('Icelandic Krona', '352'),
			'INR' => array('Indian Rupee', '356'),
			'IDR' => array('Indonesia Rupiah', '360'),
			'ILS' => array('Israel Shekel', '376'),
			'JMD' => array('Jamaican Dollar', '388'),
			'JPY' => array('Japanese yen', '392'),
			'KZT' => array('Kazakhstan Tenge', '368'),
			'KES' => array('Kenyan Shilling', '404'),
			'KWD' => array('Kuwaiti Dinar', '414'),
			'LVL' => array('Latvia Lat', '428'),
			'LBP' => array('Lebanese Pound', '422'),
			'LTL' => array('Lithuania Litas', '440'),
			'MOP' => array('Macau Pataca', '446'),
			'MKD' => array('Macedonian Denar', '807'),
			'MGA' => array('Malagascy Ariary', '969'),
			'MYR' => array('Malaysian Ringgit', '458'),
			'MTL' => array('Maltese Lira', '470'),
			'BAM' => array('Marka', '977'),
			'MUR' => array('Mauritius Rupee', '480'),
			'MXN' => array('Mexican Pesos', '484'),
			'MZM' => array('Mozambique Metical', '508'),
			'NPR' => array('Nepalese Rupee', '524'),
			'ANG' => array('Netherlands Antilles Guilder', '532'),
			'TWD' => array('New Taiwanese Dollars', '901'),
			'NZD' => array('New Zealand Dollars', '554'),
			'NIO' => array('Nicaragua Cordoba', '558'),
			'NGN' => array('Nigeria Naira', '566'),
			'KPW' => array('North Korean Won', '408'),
			'NOK' => array('Norwegian Krone', '578'),
			'OMR' => array('Omani Riyal', '512'),
			'PKR' => array('Pakistani Rupee', '586'),
			'PYG' => array('Paraguay Guarani', '600'),
			'PEN' => array('Peru New Sol', '604'),
			'PHP' => array('Philippine Pesos', '608'),
			'QAR' => array('Qatari Riyal', '634'),
			'RON' => array('Romanian New Leu', '946'),
			'RUB' => array('Russian Federation Ruble', '643'),
			'SAR' => array('Saudi Riyal', '682'),
			'CSD' => array('Serbian Dinar', '891'),
			'SCR' => array('Seychelles Rupee', '690'),
			'SGD' => array('Singapore Dollars', '702'),
			'SKK' => array('Slovak Koruna', '703'),
			'SIT' => array('Slovenia Tolar', '705'),
			'ZAR' => array('South African Rand', '710'),
			'KRW' => array('South Korean Won', '410'),
			'LKR' => array('Sri Lankan Rupee', '144'),
			'SRD' => array('Surinam Dollar', '968'),
			'SEK' => array('Swedish Krona', '752'),
			'CHF' => array('Swiss Francs', '756'),
			'TZS' => array('Tanzanian Shilling', '834'),
			'THB' => array('Thai Baht', '764'),
			'TTD' => array('Trinidad and Tobago Dollar', '780'),
			'TRY' => array('Türk Lirası', '949'),
			'AED' => array('UAE Dirham', '784'),
			'USD' => array('US Dollars', '840'),
			'UGX' => array('Ugandian Shilling', '800'),
			'UAH' => array('Ukraine Hryvna', '980'),
			'UYU' => array('Uruguayan Peso', '858'),
			'UZS' => array('Uzbekistani Som', '860'),
			'VEB' => array('Venezuela Bolivar', '862'),
			'VND' => array('Vietnam Dong', '704'),
			'AMK' => array('Zambian Kwacha', '894'),
			'ZWD' => array('Zimbabwe Dollar', '716'),
			'DEF' => array('Türk Lirası', '949'),
		);
		$c = $currencies['DEF'];
		$key = 'DEF';

		if ($by == 'iso_name') {
			if (isset($currencies[$cur])) {
				$c = $currencies[$cur];
				$key = $cur;
			}
		}
		
		if ($by == 'iso_number') {
			foreach ($currencies as $k => $curr){
				if($curr[1] == $cur){
					$c = $curr;
					$key = $k;
					break;
				}
			}
		}

		return (object) array(
				'name' => $c[0],
				'iso_code' => $key,
				'iso_code_num' => $c[1],
				'sign' => get_woocommerce_currency_symbol($cur),
		);
	}
}
