<?php
include_once(dirname( __FILE__ ) . '/../tool/GoogleCharts.class.php');

class EticStats
{
	public static function getMontlyIncome($id = 'getMontlyIncome') {
		$q = 'SELECT DATE_FORMAT(date_create, "%M") AS Month, SUM(total_pay) as totalpay, SUM(total_cart) as totalcart
		FROM ' . _DB_PREFIX_ . 'spr_transaction
		GROUP BY DATE_FORMAT(date_create, "%M")';
		$r = EticSql::executeS($q);

		if(!$r OR empty($r))
			return false;
		
		$data = array(array('Ay', 'Ödenen', 'Komisyonsuz'));
		foreach($r as $k => $v)
			$data[]= array($v['Month'], (int)$v['totalpay'], (int)($v['totalpay']-$v['totalcart']));
	
		$GoogleCharts = new GoogleCharts();
		
		$options = Array(
			'title' => 'Aylık Performans',
			'width' => 600,
			'hAxis' => Array(
				'title' => 'Ay',
				'titleTextStyle' => Array('color' => 'red')
			)
		);
		/**
		*	CHART
		*/
		return $GoogleCharts->load( 'column' , 'eticstats_'.$id )->get( $data , $options );

		
		print_r($r);
		exit;
	}

	public static function getGwUsagebyTotal($id = 'getGwUsagebyTotal'){
		$q = "SELECT SUM(total_pay) as totalpay, gateway FROM " . _DB_PREFIX_ . "spr_transaction GROUP BY gateway";
		$r = EticSql::executeS($q);
		
		if(!$r OR empty($r))
			return false;
		$headers = array('Yöntem', 'Tutar');
		$data = array($headers);
		foreach($r as $k => $v)
			$data[]= array($v['gateway'], (int)$v['totalpay']);
		
		
		
		$GoogleCharts = new GoogleCharts();
		/**
		*	OPTIONS
		*/
		$options = Array(
			'title' => 'Ciroya Göre Ödeme Yöntemleri Dağılımı',
		);
		/**
		*	CHART
		*/
		return $GoogleCharts->load( 'pie' , 'eticstats_'.$id )->get( $data , $options );
	}
	
	public static function toHtml ($chartjs, $id){
		return '
		<div id="eticstats_'.$id.'"></div>
		<!-- Javascript -->
		<script type="text/javascript">
			'.$chartjs.'
		</script>';
	}
	
	public static function getChart($function, $cart_id = false) {
		if(!method_exists('EticStats', $function))
			return 'Undefined Statistics';
		if(!$cart_id)
			$cart_id = $function.'_'.rand(0,500);
		
		$gc = call_user_func('EticStats::'.$function, $cart_id);
		
		return EticStats::toHtml($gc, $cart_id);
	}
}
?>