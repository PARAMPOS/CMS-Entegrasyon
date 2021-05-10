<?php

class ModelExtensionPaymentParam extends Model {

	public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "param_order` (
			  `param_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `created` DATETIME NOT NULL,
			  `modified` DATETIME NOT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  `currency_code` CHAR(3) NOT NULL,
			  `transaction_id` VARCHAR(24) NOT NULL,
			  `debug_data` TEXT,
			  `capture_status` INT(1) DEFAULT NULL,
			  `void_status` INT(1) DEFAULT NULL,
			  `refund_status` INT(1) DEFAULT NULL,
			  PRIMARY KEY (`param_order_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "param_transactions` (
			  `param_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `param_order_id` int(11) NOT NULL,
			  `transaction_id` VARCHAR(24) NOT NULL,
			  `created` DATETIME NOT NULL,
			  `type` ENUM('auth', 'payment', 'refund', 'void') DEFAULT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`param_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "param_card` (
			  `card_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` INT(11) NOT NULL,
			  `order_id` INT(11) NOT NULL,
			  `token` VARCHAR(50) NOT NULL,
			  `digits` VARCHAR(4) NOT NULL,
			  `expiry` VARCHAR(5) NOT NULL,
			  `type` VARCHAR(50) NOT NULL,
			  PRIMARY KEY (`card_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
	}

	public function uninstall() {
		//$this->model_setting_setting->deleteSetting($this->request->get['extension']);
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "param_order`;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "param_transactions`;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "param_card`;");
	}

	public function getOrder($order_id) {
		$qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "param_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($qry->num_rows) {
			$order = $qry->row;
			$order['transactions'] = $this->getTransactions($order['param_order_id']);
			return $order;
		} else {
			return false;
		}
	}
	
	public function updateCaptureStatus($param_order_id, $status) {
		$this->db->query("UPDATE `" . DB_PREFIX . "param_order` SET `capture_status` = '" . (int)$status . "' WHERE `param_order_id` = '" . (int)$param_order_id . "'");
	}

	public function updateTransactionId($param_order_id, $transaction_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "param_order` SET `transaction_id` = '" . $transaction_id . "' WHERE `param_order_id` = '" . (int)$param_order_id . "'");
	}

	public function updateRefundStatus($param_order_id, $status) {
		$this->db->query("UPDATE `" . DB_PREFIX . "param_order` SET `refund_status` = '" . (int)$status . "' WHERE `param_order_id` = '" . (int)$param_order_id . "'");
	}

	private function getTransactions($param_order_id) {
		$qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "param_transactions` WHERE `param_order_id` = '" . (int)$param_order_id . "'");

		if ($qry->num_rows) {
			return $qry->rows;
		} else {
			return false;
		}
	}

	public function addTransaction($param_order_id, $transactionid, $type, $total, $currency) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "param_transactions` SET `param_order_id` = '" . (int)$param_order_id . "', `created` = NOW(), `transaction_id` = '" . $this->db->escape($transactionid) . "', `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($total, $currency, false, false) . "'");
	}

	public function getTotalCaptured($param_order_id) {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "param_transactions` WHERE `param_order_id` = '" . (int)$param_order_id . "' AND `type` = 'payment' ");

		return (double)$query->row['total'];
	}

	public function getTotalRefunded($param_order_id) {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "param_transactions` WHERE `param_order_id` = '" . (int)$param_order_id . "' AND `type` = 'refund'");

		return (double)$query->row['total'];
	}

}