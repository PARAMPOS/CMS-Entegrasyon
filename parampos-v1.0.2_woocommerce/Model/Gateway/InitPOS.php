<?php

class InitPOS
{
	public function pay($transaction)
	{   
		$transaction->tds = true;
		$CLIENT_CODE = $transaction->gateway_params->client_code;
		$CLIENT_USERNAME = $transaction->gateway_params->client_username;
		$CLIENT_PASSWORD = $transaction->gateway_params->client_password;
		$GUID = $transaction->gateway_params->guid;
		$MODE = $transaction->gateway_params->test_mode == "on" ? "TEST" : "PROD";
		$serviceUrl = $transaction->serviceUrl;
		$rate = 0;
		
        $bin = new Bin($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
		$binResponse = $bin->send($transaction->cc_number)->fetchBIN();
		
		$posId = $binResponse["posId"];  
		$cc = new InstallmentForUser($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
		$response = $cc->send()->fetchInstallment();
		
		$prerate = str_pad($transaction->installment, 2, '0', STR_PAD_LEFT); 

		foreach ($response as $key => $resp) {
			if ($resp[0]["SanalPOS_ID"] == $posId) { 
				$rate = $resp[0]["MO_$prerate"]; 
			} 
		}  

		if ($rate == -2) {
			$transaction->resultCode = '-1';
			$transaction->resultMessage = "Kartınız ".$transaction->installment." taksit desteklemiyor !"; 
			$transaction->result = false;
			return $transaction;
		}   
			
		$transactionId = $transaction->trId;
        
		$rate_edit = (100 + $rate); 
		$cartTotal = $transaction->cartTotalIncFee * 100 / $rate_edit;  
		$amount = $cartTotal + ($cartTotal * $rate / 100);  
		$orderId 	= $transaction->orderId;
		$ccOwner	= $transaction->ccName;
		$ccNumber	= $transaction->ccNumber;
		$ccMonth	= str_pad($transaction->ccExpireMonth, 2, "0", STR_PAD_LEFT);
		$ccYear	    = "20" . str_pad(substr($transaction->ccExpireYear, -2) , 2 , "0", STR_PAD_LEFT);
		$ccCVV		= $transaction->ccCVV; 
		$clientIp	= $transaction->ip;
		$phone		=  $transaction->customerPhone;
		$installment = $transaction->installment;  
		$transaction->boid 	= $transaction->cartId;
		$totalAmount = number_format($amount, 2, ',', ""); 
		$amount		= number_format($cartTotal, 2, ',', "");
		if ($transaction->tds) {
			$transaction->resultCode = '3D-R';
			$transaction->resultMessage = '3D formu oluşturuldu.';
			$transaction->result = false;
			$transaction->tds = true;
			$transaction->saveTransaction();
            
			try {
				$saleObj = new Pay3d($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE, $serviceUrl);
				$saleObj->send( $posId, $ccOwner, $ccNumber, $ccMonth,  $ccYear, $ccCVV, $phone, $transaction->failUrl, $transaction->successUrl,
					$orderId, $transaction->shopName, $installment, $amount, $totalAmount, $transactionId, $clientIp, $_SERVER['HTTP_REFERER'], "", "", "", "", ""
				);
                
				$paramResponse = $saleObj->parse();
				$transaction->boid = $paramResponse['Islem_ID'];
				$transaction->resultMessage = $paramResponse["Sonuc_Str"];
				$transaction->result = (string) $paramResponse['Sonuc'] > 0 ? true : false;
				if($transaction->result)
					$transaction->redirectUrl = $paramResponse['UCD_URL'];
				
			} catch (Exception $e) {
				$transaction->resultCode = 'TURKPOS-LIB-ERROR';
				$transaction->resultMessage = $e->getMessage();
				$transaction->result = false;
			}
			return $transaction;
		}
		return $transaction; 
	}
}
