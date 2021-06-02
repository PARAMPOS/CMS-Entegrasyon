<?php

class ParamWUI extends ParamUI
{

	function __construct()
	{
		$this->store_uri = get_site_url();
		$this->store_url = get_permalink(wc_get_page_id('shop'));
		$this->uri = plugins_url() . '/sanalpospro/';
		$this->url = plugins_url() . '/sanalpospro/';
	}

	public function l($txt)
	{ // translate
		return __($txt, 'sanalpospro');
	}

	public function displayPrice($price, $currency = null)
	{
		return $price . ' ' . $currency;
	}

	public function addCSS($file, $external = false)
	{
		return '<link rel="stylesheet" href="' . $this->uri . $file . '" type="text/css" media="all">';
	}

	public function displayProductInstallments($price)
	{

		$prices = ParamInstallment::getRates($price);
		if (count($prices) < 1)
			return;
		$return = '<div class="row">';
		$block_count = 0;
		foreach ($prices as $f => $v) {
			$block_count++;
			if ($block_count == 4) {
				$return .= '</div><div class="row">';
			}
			$return .= '<div class="col-lg-4 col-sm-4 col-xs-6 spr_bank">
				<div class="nst_container ' . $f . '">
					<div class="block_title" align="center"><img src="' . $this->uri . 'img/cards/' . $f . '.png"></div>';
			$return .= '<table class="table">
						<tr>
							<th>' . $this->l('Taksit Sayısı') . '</th>
							<th>' . $this->l('Aylık Ödeme') . '</th>
							<th>' . $this->l('Toplam Tutar') . '</th>
						</tr>';
			foreach ($v as $k => $ins) {
				$return .= '<tr class="' . ($k % 2 ? $f . '-odd' : '' ) . '">
				<td>' . $k . '</td>
				<td>' . ParamWUI::displayPrice($ins['month']) . '</td>
				<td>' . ParamWUI::displayPrice($ins['total']) . '</td>
			</tr>';
			}
			$return .= '</table></div></div>';
		}
		$return .= '<div class="col-lg-4 col-sm-4 col-xs-6 spr_bank">
				<div class="nst_container">
					<div class="block_title"><h3>' . $this->l('Diğer Kartlar') . '</h3></div>
					' . $this->l('Tüm kredi ve bankamatik kartları ile tek çekim olarak ödeme yapabilirsiniz.') . '
					<hr/>
					<img class="col-sm-12 img-responsive" src="' . $this->uri . 'img/master_visa_aexpress.png"/>
					</div>
					</div>';
		$return .= '</div></section>';
		return $return;
	}

	public function displayAdminOrder($tr)
	{
		
		$order = new WC_Order($tr->id_order);
		$order->get_currency();
		$cur = ParamTools::getCurrency($tr->currency_number, 'iso_number');
		$currency = $cur->iso_code;
		$t = '
			<li class="wide">
			<hr/>
		<div>
			<div align="center">
				<h2 align="center" class="spp_head">
					' . $this->l('Credit Card Process Details') . '
				</h2>
				<br/>
					<a href="https://param.com.tr" target="_blank">
						<img src="https://param.com.tr/images/param-logo-v3-mor.svg" width="180px"/>
					</a> <br/>
				<hr/>
				<table class="wp-list-table widefat fixed striped posts">
					<tr>
						<td>' . $this->l('POS answer:') . ' ' . $tr->result_code . '<br/>
						<span class="badge">' . $tr->date_update . '</span>'
					. '</td>'
					. '<td>#' . $tr->boid . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Total Amount') . '</td>
						<td><span style="font-size:2em;">' . $this->displayPrice($tr->total_pay) . '</span>
						' . $currency . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Customer Fee Commission') . '</td>
						<td><span class="badge badge-warning">' . $this->displayPrice($tr->total_pay - $tr->total_cart, $currency) . '</span></td>
					</tr>
					<tr>
						<td>' . $this->l('POS System Fee') . '</td>
						<td><span class="badge badge-danger">' . $this->displayPrice($tr->gateway_fee, $currency) . '</span></td>
					</tr>
					<tr>
						<td>' . $this->l('E-Shop Remaining Amount') . '</td>
						<td><span class="badge badge-success" style="font-size:2em;">' . $this->displayPrice($tr->total_pay - $tr->gateway_fee) . '</span>
							' . $currency . '</td>
					</tr>
					<tr>
						<td colspan="2">
							' . $this->l('IP Address') . ' <span class="badge">' . $tr->cip . '</span> <br/>
							' . $this->l('Transaction number') . ' <span class="badge">' . $tr->boid . '</span>
							<br/>	' . $tr->date_update . '
						</td>
					</tr>
				</table>
				<div class="row align-center"><br/>
					<small>
						Bu bilgilerde bir hata olduğu düşünüyorsanız <a href="https://param.com.tr/Iletisim.aspx">Hata Bildirimi</a>
					</small>
				</div>
			</div>
				<hr/>
			<div align="center">
				<h2 align="center" class="spp_head">
					' . $this->l('Credit Card Info') . '
				</h2>
				<table class="wp-list-table widefat fixed striped posts">
					<tr>
						<td>' . $this->l('Installment') . '</td>
						<td>' . $tr->installment . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Card Name') . '</td>
						<td>' . $tr->cc_name . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Card No') . '</td>
						<td>' . $tr->cc_number . '</td>
					</tr>
				</table>
			</div>
			</li>';
		return $t;
	}
}