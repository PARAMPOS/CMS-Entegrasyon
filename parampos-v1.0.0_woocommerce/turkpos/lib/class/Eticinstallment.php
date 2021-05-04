<?php

class EticInstallment
{

	public $gateway;
	public $family;
	public $divisor;
	public $rate;
	public $fee;
	public $exists = false;

	function __construct($family, $divisor = 1)
	{
		$row = EticSql::getRow('spr_installment', array(
				'family' => Etictools::escape($family),
				'divisor' => (int) $divisor));
		if (!$row)
			return false;
		foreach ($row as $k => $v)
			$this->{$k} = $v;
		$this->exists = true;
		return $this;
	}

	public static function getAll()
	{
		return EticSql::getRows('spr_installment');
	}

	public static function getOrdered()
	{
		$return = array();
		foreach (EticConfig::$families as $family) {
			$rates = EticInstallment::getByFamily($family);
			if ($rates)
				$return[$family] = $rates;
		}
		return $return;
	}

	public static function getByFamily($family, $divisor = false)
	{
		if ($divisor)
			return EticSql::getRow('spr_installment', array(
					'family' => Etictools::escape($family),
					'divisor' => (int) $divisor));
		return EticSql::getRows('spr_installment', 'family', Etictools::escape($family), false, array('by' => 'divisor', 'type' => 'ASC'));
	}

	public static function getByGateway($gateway)
	{
		return EticSql::getRows('spr_installment', 'gateway', Etictools::escape($gateway));
	}
	/*
	 * Array $installment
	 */

	public static function save($installment)
	{
		EticInstallment::deletebyFamily($installment['family'], $installment['divisor']);
		return EticSql::insertRow('spr_installment', $installment);
	}
	/*
	 * String $gateway 
	 * int $divisor (deletes all if false)
	 */

	public static function delete($gateway, $divisor = false)
	{
		if ($divisor)
			return EticSql::deleteRow('spr_installment', array(
					'gateway' => Etictools::escape($gateway),
					'divisor' => (int) $divisor
			));
		return EticSql::deleteRows('spr_installment', 'gateway', Etictools::escape($gateway));
	}

	public static function deletebyFamily($family, $divisor = false)
	{
		if ($divisor)
			return EticSql::deleteRow('spr_installment', array(
					'family' => Etictools::escape($family),
					'divisor' => (int) $divisor
			));
		return EticSql::deleteRows('spr_installment', 'gateway', Etictools::escape($gateway));
	}

	public static function checkInstallments()
	{
		//EticSql::getRows('spr_installment', array)
	}

	public static function getRates($price = 100)
	{

		$rates = array();
		foreach (EticInstallment::getOrdered() as $fam) {
			foreach ($fam as $ins) {
				$total = number_format((1 + ((float) $ins['rate'] / 100)) * $price, 2, '.', '');
				$monthly = number_format($total / $ins['divisor'], 2, '.', '');
				$rates[$ins['family']][$ins['divisor']] = array('month' => $monthly, 'total' => $total, 'rate' => $ins['rate']);
			}
		}
		return $rates;
	}

	public static function getDefaultRate()
	{
		if ($def = EticSql::getRow('spr_installment', 'family', 'all'))
			return $def;
		if (!$gw = EticSql::getRow('spr_gateway', 'active', true))
			return false;
		if (EticSql::insertRow('spr_installment', array('gateway' => $gw['name'], 'family' => 'all', 'rate' => 0, 'fee' => 0, 'divisor' => 1)))
			return EticInstallment::getDefaultRate();
	}

	public static function calcDefaultRate($amount)
	{
		$rate = EticInstallment::getDefaultRate();
		$total = number_format((1 + ((float) $rate['rate'] / 100)) * $amount, 2, '.', '');
		$monthly = number_format($total / $rate['divisor'], 2, '.', '');
		return array('month' => $monthly, 'total' => $total, 'rate' => $rate['rate']);
	}

	public static function getRestrictedProducts($id_cart)
	{
		$res_pros = array();
		$res_cats = EticConfig::getResCats();
		if ($res_cats == 'off' OR ! is_array($res_cats) OR empty($res_cats))
			return array();
		$cart = New Cart($id_cart);
		$products = $cart->getProducts();
		foreach ($products as $p) {
			if (in_array((string) $p['id_category_default'], $res_cats))
				$res_pros [] = $p['id_product'];
		}
		return $res_pros;
	}

	public static function getProductRestriction($id_category_default)
	{
		$res_cats = EticConfig::getResCats();
		if ($res_cats == 'off' OR ! is_array($res_cats) OR empty($res_cats))
			return false;
		if (in_array((string) $id_category_default, $res_cats))
			return true;
		return false;
	}

	public static function getInstallment($id_category_default)
	{
		
	}
}
