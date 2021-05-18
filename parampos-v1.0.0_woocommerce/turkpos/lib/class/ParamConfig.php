<?php

Class ParamConfig
{

	public static $order_themes = array(
		'pro' => 'PRO!tema (Önerilir)',

	);
	public static $installment_themes = array(
		'color' => 'Renkli (Önerilir)',
		'simple' => 'Basit (Renksiz)',
		'white' => 'Beyaz (Resmi)',
		'colorize' => 'Colorize (Seksi) '
	);
	public static $families = array(
		'axess', 'bonus', 'maximum', 'cardfinans', 'world', 'paraf', 'advantage', 'combo', 'miles-smiles'
	);
	public static $messages = array();
	public static $gateways;

	public static function get($key)
	{
		return get_option($key);
	}

	public static function set($key, $value)
	{
		return update_option($key, $value);
	}

}
