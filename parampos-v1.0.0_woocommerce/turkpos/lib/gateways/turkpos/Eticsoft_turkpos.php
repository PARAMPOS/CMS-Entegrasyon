<?php
require_once("src/loader.php");

class EticSoft_turkpos
{

	var $version = 201110;

	function pay($tr)
	{   
		$tr->tds = true;
		$CLIENT_CODE = $tr->gateway_params->client_code;
		$CLIENT_USERNAME = $tr->gateway_params->client_username;
		$CLIENT_PASSWORD = $tr->gateway_params->client_password;
		$GUID = $tr->gateway_params->guid;
		$MODE = $tr->gateway_params->test_mode == "on" ? "TEST" : "PROD";
		$serviceUrl = $tr->serviceUrl;
		$rate = 0;
		$bin = new param\Bin($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
		$bin->send($tr->cc_number);
		$bin_response = $bin->parse();
		
		$posId = $bin_response["posId"];  
		$cc = new param\GetInstallmentPlanForUser($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
		$cc->send();
		$response = $cc->parse();  
		
		$prerate = str_pad($tr->installment, 2, '0', STR_PAD_LEFT); 

		foreach ($response as $key => $resp) {
			if ($resp[0]["SanalPOS_ID"] == $posId) { 
				$rate = $resp[0]["MO_$prerate"]; 
			} 
		}  

		if ($rate == -2) {
			$tr->result_code = '-1';
			$tr->result_message = "Kartınız ".$tr->installment." taksit desteklemiyor !"; 
			$tr->result = false;
			return $tr;
		}   
			
		$transactionId = $tr->id_transaction;
		// set new rates
		$rate_edit = (100 + $rate); 
		$t_cart = $tr->total_pay * 100 / $rate_edit;  
		$t_amount = $t_cart + ($t_cart * $rate / 100);  
		$order_id 	= $tr->id_order;
		$cc_holder	= $tr->cc_name;
		$cc_number	= $tr->cc_number;
		$cc_month	= str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
		$cc_year	= "20".str_pad(substr($tr->cc_expire_year, -2) ,2 ,"0", STR_PAD_LEFT);
		$cc_cvv		= $tr->cc_cvv; 
		$ClientIp	= $tr->cip;
		$phone		=  $tr->customer_phone;
		$installment = $tr->installment;  
		$tr->boid 	= $tr->id_cart;
		$total_amount = number_format($t_amount, 2, ',',""); 
		$amount		= number_format($t_cart, 2, ',',"");
		if ($tr->tds) {
			$tr->result_code = '3D-R';
			$tr->result_message = '3D formu oluşturuldu.';
			$tr->result = false;
			$tr->tds = true;
			$tr->save();
			
			try {
				$saleObj = new param\Sale3d($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
				$saleObj->send( $posId, $cc_holder, $cc_number, $cc_month,  $cc_year, $cc_cvv, $phone, $tr->fail_url, $tr->ok_url,
					$order_id, $tr->shop_name, $installment, $amount, $total_amount, $transactionId, $ClientIp, $_SERVER['HTTP_REFERER'], "", "", "", "", ""
				); 
				$paramResponse = $saleObj->parse();  
				$tr->boid = $paramResponse['Islem_ID'];
				$tr->result_message = $paramResponse["Sonuc_Str"];
				$tr->result = (string) $paramResponse['Sonuc'] > 0 ? true : false;
				if($tr->result)
					$tr->tds_echo = $paramResponse['UCD_URL'];
				
			} catch (Exception $e) {
				$tr->result_code = 'TURKPOS-LIB-ERROR';
				$tr->result_message = $e->getMessage();
				$tr->debug($tr->result_code . ' ' . $tr->result_message);
				$tr->result = false;
			}
			return $tr;
		}
		return $tr; 
	}


	public function tdValidate($tr)
	{

		if (!isset($_POST['TURKPOS_RETVAL_Sonuc_Str']) ) {
			$tr->result_message = "Eksik Parametre " . Etictools::getValue('errorMessage');
			$tr->result = false;
			$tr->result_code = 0;
			return $tr;
		}

		$response = $_POST; 

		if ($response["TURKPOS_RETVAL_Dekont_ID"] > 0) {
			$tr->result = true;
			$tr->result_code = $response['TURKPOS_RETVAL_Sonuc'];
			$tr->result_message = $response['TURKPOS_RETVAL_Sonuc_Str'];
			return $tr;
		}
		else {
			$tr->result = false;
			$tr->result_code = $response['TURKPOS_RETVAL_Sonuc'];
			$tr->result_message = $response['TURKPOS_RETVAL_Sonuc_Str'];
			return $tr;
		} 
	}





}
