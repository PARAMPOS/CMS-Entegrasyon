<?php 
    function param_activate()
    {
        $transactionTable = "
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "param_transaction` (
                `trId` int(11) NOT NULL AUTO_INCREMENT,
                `ccName` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                `ccNumber` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
                `cartId` int(11) NOT NULL,
                `currencyCode` int(11) NOT NULL,
                `orderId` int(11) DEFAULT NULL,
                `customerId` int(11) NOT NULL,
                `cartTotalExcFee` float(10,2) DEFAULT NULL,
                `cartTotalIncFee` float(10,2) DEFAULT NULL,
                `gatewayFee` float(10,2) DEFAULT NULL,
                `installment` int(2) NULL,
                `ip` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
                `testMode` int(1) NOT NULL,
                `tds` int(1) NOT NULL,
                `boid` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                `resultCode` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
                `resultMessage` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
                `result` int(1) NOT NULL,
                `trCreatedAt` datetime NOT NULL,
                `trUpdatedAt` datetime DEFAULT NULL,
                PRIMARY KEY (`trId`)
                ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $debugTable = "
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "param_debug` (
                `trId` int(11) NOT NULL,
                `debug` text NULL,
                UNIQUE KEY `trId` (`trId`)
                ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
        return dbDelta($transactionTable) && dbDelta($debugTable);
    }