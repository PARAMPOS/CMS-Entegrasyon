<?php

class OrderView
{

	public $url;
	public $uri;
	public $store_url;
	public $store_uri;
	public $path;
	public $store_path;
	public $js_dir;
	public $css_dir;
	public $force_bootstrap;
	public $force_jquery;
	public $module;
	public $context = '';

	public function display()
	{
		return $this->context;
	}

	public function l($txt)
	{
		return __($txt, 'turkpos');
	}

	public function displayCustomerOrder($tr)
	{
		$currency = new Currency($tr->id_currency);
		$t = '
		<div>
			<div class="col-md-6" >
				<div class="panel">
					<div class="panel-heading">
						<i class="icon-credit-card"></i>
						' . $this->l('Credit Card Process Details') . '
						<span class="badge">
							' . $tr->gateway . '#' . $tr->boid . '
						</span>
					</div>
					<table class="table">
						<tr>
							<td>' . $this->l('POS answer:') . '#' . $tr->resultCode . ' ' . $tr->resultMessage . '<br/>
								' . $this->l('Date') . ' <span class="badge">' . $tr->date_update . '</span></td>
						</tr>
						<tr>
							<td>' . $this->l('Total Amount') . '</td>
							<td><span style="font-size:2em;">' . $this->displayPrice($tr->total_pay, $currency) . '</span></td>
						</tr>
						<tr>
							<td colspan="2">
								' . $this->l('IP Address') . ' <span class="badge">' . $tr->cip . '</span> 
								' . $this->l('Transaction number') . ' <span class="badge">' . $tr->boid . '</span>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="col-md-6" >
				<div class="row">
					<div class="panel">
						<div class="panel-heading">
							<i class="icon-credit-card"></i>
							' . $this->l('Credit Card Info') . '
							<span class="badge">
								' . $tr->cc_name . '
							</span>
						</div>
						<table class="table">
							<tr>
								<td>' . $this->l('Card Type') . '</td>
								<td><img src="' . $this->url . 'img/cards/' . ($tr->family != '' ? $tr->family : 'default') . '.png" /></td>
							<td>' . $this->l('Installment') . '</td>
							<td>' . $tr->installment . '</td>
							</tr>
							<tr>
								<td>' . $this->l('Card Name') . '</td>
								<td>' . $tr->cc_name . '</td>
								<td>' . $this->l('Card No') . '</td>
								<td>' . $tr->cc_number . '</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>';
		$this->context .= $t;
		return $this->display();
	}

	public function displayAdminOrder($tr)
	{
		$order = new WC_Order($tr->orderId);
		$helper = new Data();
		$order->get_currency();
		$cur = $helper->getCurrency($tr->currencyNumber, 'iso_number');
		$currency = $cur->iso_code;
		$t = '<li class="wide">
			<hr/>
		<div>
			<div align="center">
				<h2 align="center">
					' . $this->l('Credit Card Process Details') . '
				</h2>
				<br/>
					<a href="https://param.com.tr" target="_blank">
						<img src="/turkpos/views/images/param-logo-v3-mor.svg" width="180px"/>
					</a> <br/>
				<hr/>
				<table class="wp-list-table widefat fixed striped posts">
					<tr>
						<td>' . $this->l('POS answer:') . ' ' . $tr->resultCode . '<br/>
						<span class="badge">' . $tr->trUpdatedAt . '</span>'
					. '</td>'
					. '<td>#' . $tr->boid . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Total Amount') . '</td>
						<td><span style="font-size:2em;">' . $this->displayPrice($tr->cartTotalExcFee) . '</span>
						' . $currency . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Customer Fee Commission') . '</td>
						<td><span class="badge badge-warning">' . $this->displayPrice($tr->cartTotalExcFee - $tr->cartTotalIncFee, $currency) . '</span></td>
					</tr>
					<tr>
						<td>' . $this->l('POS System Fee') . '</td>
						<td><span class="badge badge-danger">' . $this->displayPrice($tr->gatewayFee, $currency) . '</span></td>
					</tr>
					<tr>
						<td>' . $this->l('E-Shop Remaining Amount') . '</td>
						<td><span class="badge badge-success" style="font-size:2em;">' . $this->displayPrice($tr->cartTotalExcFee - $tr->gatewayFee) . '</span>
							' . $currency . '</td>
					</tr>
					<tr>
						<td colspan="2">
							' . $this->l('IP Address') . ' <span class="badge">' . $tr->ip . '</span> <br/>
							' . $this->l('Transaction number') . ' <span class="badge">' . $tr->boid . '</span>
							<br/>	' . $tr->trUpdatedAt . '
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
						<td>' . $tr->ccName . '</td>
					</tr>
					<tr>
						<td>' . $this->l('Card No') . '</td>
						<td>' . $tr->ccNumber . '</td>
					</tr>
				</table>
			</div>
			</li>';
		return $t;
	}

	public function displayPrice($price, $currency = null)
	{
		return $price . ' ' . $currency;
	}
}
