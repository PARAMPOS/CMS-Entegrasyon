<?php
/**
 * Param Prestashop Payment Module - Payment response page
 *
 * @author    Param www.param.com.tr
 * @version   1.0.0
 */
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/parampos.php');
$param = new Parampos();
$response = $param->getAccessCodeResult();