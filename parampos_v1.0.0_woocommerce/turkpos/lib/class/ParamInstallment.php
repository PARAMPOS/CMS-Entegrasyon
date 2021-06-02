<?php

class ParamInstallment
{

	public $gateway;
	public $family;
	public $divisor;
	public $rate;
	public $fee;
	public $exists = false;

	function __construct($family, $divisor = 1)
	{
		$row = ParamQuery::getRow('spr_installment', array(
				'family' => ParamTools::escape($family),
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
		return ParamQuery::getRows('spr_installment');
	}

	public static function getOrdered()
	{
		$return = array();
		foreach (ParamConfig::$families as $family) {
			$rates = ParamInstallment::getByFamily($family);
			if ($rates)
				$return[$family] = $rates;
		}
		return $return;
	}

	public static function getByFamily($family, $divisor = false)
	{
		if ($divisor)
			return ParamQuery::getRow('spr_installment', array(
					'family' => ParamTools::escape($family),
					'divisor' => (int) $divisor));
		return ParamQuery::getRows('spr_installment', 'family', ParamTools::escape($family), false, array('by' => 'divisor', 'type' => 'ASC'));
	}

	public static function getByGateway($gateway)
	{
		return ParamQuery::getRows('spr_installment', 'gateway', ParamTools::escape($gateway));
	}
	/*
	 * Array $installment
	 */

	public static function save($installment)
	{
		ParamInstallment::deletebyFamily($installment['family'], $installment['divisor']);
		return ParamQuery::insertRow('spr_installment', $installment);
	}
	/*
	 * String $gateway 
	 * int $divisor (deletes all if false)
	 */

	public static function delete($gateway, $divisor = false)
	{
		if ($divisor)
			return ParamQuery::deleteRow('spr_installment', array(
					'gateway' => ParamTools::escape($gateway),
					'divisor' => (int) $divisor
			));
		return ParamQuery::deleteRows('spr_installment', 'gateway', ParamTools::escape($gateway));
	}

	public static function deletebyFamily($family, $divisor = false)
	{
		if ($divisor)
			return ParamQuery::deleteRow('spr_installment', array(
					'family' => ParamTools::escape($family),
					'divisor' => (int) $divisor
			));
		return ParamQuery::deleteRows('spr_installment', 'gateway', ParamTools::escape($gateway));
	}

	public static function checkInstallments()
	{
	}

	public static function getRates($price = 100)
	{

		$rates = array();
		foreach (ParamInstallment::getOrdered() as $fam) {
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
		if ($def = ParamQuery::getRow('spr_installment', 'family', 'all'))
			return $def;
		if (!$gw = ParamQuery::getRow('spr_gateway', 'active', true))
			return false;
		if (ParamQuery::insertRow('spr_installment', array('gateway' => $gw['name'], 'family' => 'all', 'rate' => 0, 'fee' => 0, 'divisor' => 1)))
			return ParamInstallment::getDefaultRate();
	}

	public static function calcDefaultRate($amount)
	{
		$rate = ParamInstallment::getDefaultRate();
		$total = number_format((1 + ((float) $rate['rate'] / 100)) * $amount, 2, '.', '');
		$monthly = number_format($total / $rate['divisor'], 2, '.', '');
		return array('month' => $monthly, 'total' => $total, 'rate' => $rate['rate']);
	}

}
