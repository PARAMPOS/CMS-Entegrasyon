<?php

// Sql Helper //
class ParamQuery
{

	function __construct()
	{
		$this->time = time();
	}

	public static function fix($v)
	{
		return $v;
		return (pSql($v));
	}

	public static function execute($q)
	{
		global $wpdb;
		return $wpdb->query($q);
	}

	public static function executeS($q)
	{
		global $wpdb;
		return $wpdb->get_results($q, ARRAY_A);
	}

	public static function getRow($table, $where, $what = false, $deb = false)
	{
		$q = "SELECT * FROM `" . _DB_PREFIX_ . "$table` WHERE ";
		if (is_array($where)) {
			$i = count($where);
			foreach ($where as $k => $v) {
				$i--;
				$q .= '`' . $k . '` = \'' . ParamQuery::fix($v) . '\' ';
				if ($i != 0)
					$q .= ' AND ';
			}
		}
		else {
			$q .= "`$where`='" . ParamQuery::fix($what) . "'";
		}
		global $wpdb;
		return $wpdb->get_row($q, ARRAY_A);
	}

	public static function deleteRow($table, $where, $what = null, $deb = false)
	{
		$q = "DELETE FROM `" . _DB_PREFIX_ . "$table` WHERE ";
		if (is_array($where)) {
			$i = count($where);
			foreach ($where as $k => $v) {
				$i--;
				$q .= '`' . $k . '` = \'' . ParamQuery::fix($v) . '\' ';
				if ($i != 0)
					$q .= ' AND ';
			}
		}
		else {
			$q .= "`$where`='" . ParamQuery::fix($what) . "'";
		}
		$q .= " LIMIT 1";
		return ParamQuery::execute($q);
	}

	public static function deleteRows($table, $where, $what)
	{
		$q = "DELETE FROM `" . _DB_PREFIX_ . "$table` WHERE `$where` = '" . ParamQuery::fix($what) . "'";
		return ParamQuery::execute($q);
	}

	public static function getRows($table, $where = false, $what = false, $limit = false, $order = false, $deb = false)
	{
		$result = array();
		$q = "SELECT * FROM " . _DB_PREFIX_ . "$table ";
		if (!$where)
			return ParamQuery::ExecuteS($q);
		else
			$q .= ' WHERE ';
		if (is_array($where)) {
			$i = count($where);
			foreach ($where as $k => $v) {
				$i--;
				$q .= '`' . $k . '` = \'' . ParamQuery::fix($v) . '\' ';
				if ($i != 0)
					$q .= ' AND ';
			}
		} else
			$q .= " `$where` = '" . ParamQuery::fix($what) . "' ";
		if ($order) {
			if (is_array($order))
				$q .= "ORDER BY `" . $order['by'] . "` " . $order['type'] . ' ';
			else
				$q .= "ORDER BY `$order` ";
		}
		if ($limit)
			$q .= " LIMIT $limit ";
		return ParamQuery::ExecuteS($q);
	}

	public static function getRowsAll($table, $limit = "0, 300", $order = false, $order_type = 'ASC')
	{
		$result = array();
		$q = "SELECT * FROM `" . _DB_PREFIX_ . "$table` ";
		if ($order)
			$q .= " ORDER BY `$order` $order_type";
		if ($limit)
			$q .= " LIMIT $limit ";
		return ParamQuery::ExecuteS($q);
	}

	public static function updateRow($table, $array, $where, $what = null, $deb = false)
	{
		$q = "UPDATE `" . _DB_PREFIX_ . "$table` SET ";
		$i = count($array);
		foreach ($array as $k => $v) {
			$q .= '`' . $k . '` = ' . "'" . ParamQuery::fix($v) . "'";
			$i--;
			if ($i > 0)
				$q .= " ,\n";
		}
		$q .= ' WHERE ';
		if (is_array($where)) {
			$i = count($where);
			foreach ($where as $k => $v) {
				$i--;
				$q .= '`' . $k . '` = \'' . ParamQuery::fix($v) . '\' ';
				if ($i != 0)
					$q .= ' AND ';
			}
		} else
			$q .= "`$where` = '" . ParamQuery::fix($what) . "' LIMIT 1";
//		if($table == 'spr_installment')
//		die($q);
		return ParamQuery::execute($q);
	}

	public static function insertRow($table, $array, $deb = false)
	{
		global $wpdb;
		if ($wpdb->insert(_DB_PREFIX_ .$table, $array))
			return $wpdb->insert_id;
		return false;
	}

	public static function Count($table, $where = false, $what = false, $count = false, $deb = false)
	{
		if (!$count)
			$q = 'SELECT COUNT(*) as `total` FROM `' . _DB_PREFIX_ . $table . '`';
		else
			$q = 'SELECT COUNT(`' . $count . '`) as `total` FROM `' . _DB_PREFIX_ . $table . '`';
		if (!$where)
			$q .= "";
		else
			$q .= ' WHERE ';
		if (is_array($where)) {
			$i = count($where);
			foreach ($where as $k => $v) {
				$i--;
				$q .= '`' . $k . '` = \'' . ParamQuery::fix($v) . '\' ';
				if ($i != 0)
					$q .= ' AND ';
			}
		}
		else if ($where != false AND $what)
			$q .= "`$where` = '" . ParamQuery::fix($what) . "' ";
		else
			$q .= '';
		return ParamQuery::execute($q);
	}

	public static function XCount($table, $where = false, $what = false, $q = "", $deb = false)
	{
		$q = "SELECT * FROM `" . _DB_PREFIX_ . "$table` ";
		if (!$where)
			$q .= "";
		else if (is_array($where)) {
			$i = count($where);
			foreach ($where as $k => $v) {
				$i--;
				$q .= '`' . $k . '` = \'' . ParamQuery::fix($v) . '\' ';
				if ($i != 0)
					$q .= ' AND ';
			}
		} else
			$q .= "`$where` = '$what' LIMIT 1";
		return ParamQuery::execute($q);
	}

	public static function tableExists($table)
	{

		// Try a select statement against the table
		// Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
		try {
			$result = ParamQuery::execute("SELECT 1 FROM " . _DB_PREFIX_ . "$table LIMIT 1");
		} catch (Exception $e) {
			// We got an exception == table not found
			return FALSE;
		}

		// Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
		return $result !== FALSE;
	}
}

?>