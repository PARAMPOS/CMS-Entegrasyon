<?php 
    function param_activate()
    {
        $gatewayTable = "
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "param_gateway` (
                `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
                `method` enum('cc', 'wire', 'other') NOT NULL,
                `full_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                `lib` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
                `active` tinyint(1) NOT NULL DEFAULT '1',
                `params` text COLLATE utf8_unicode_ci NOT NULL,
                UNIQUE KEY (`name`)
            ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $installmentTable = "
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "param_installment` (
                `gateway` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
                `family` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
                `divisor` int(3) NOT NULL,
                `rate` decimal(4,2) NOT NULL,
                `fee` decimal(4,2) NOT NULL DEFAULT '0.00',
                UNIQUE KEY `gateway` (`family`,`divisor`)
            ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $transactionTable = "
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "param_transaction` (
                `id_transaction` int(11) NOT NULL AUTO_INCREMENT,
                `notify` tinyint(1) NOT NULL DEFAULT '0',
                `cc_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                `cc_number` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
                `gateway` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
                `family` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
                `id_cart` int(11) NOT NULL,
                `id_currency` int(11) NOT NULL,
                `id_order` int(11) DEFAULT NULL,
                `id_customer` int(11) NOT NULL,
                `total_cart` float(10,2) DEFAULT NULL,
                `total_pay` float(10,2) DEFAULT NULL,
                `gateway_fee` float(10,2) DEFAULT NULL,
                `installment` int(2) NULL,
                `cip` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
                `test_mode` int(1) NOT NULL,
                `tds` int(1) NOT NULL,
                `boid` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                `result_code` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
                `result_message` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
                `result` int(1) NOT NULL,
                `detail` TEXT DEFAULT NULL,
                `date_create` datetime NOT NULL,
                `date_update` datetime DEFAULT NULL,
                PRIMARY KEY (`id_transaction`)
                ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $debugTable = "
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "param_debug` (
                `id_transaction` int(11) NOT NULL,
                `debug` text NULL,
                UNIQUE KEY `id_transaction` (`id_transaction`)
                ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
        return dbDelta($gatewayTable) && dbDelta($installmentTable) && dbDelta($transactionTable) && dbDelta($debugTable);
    }