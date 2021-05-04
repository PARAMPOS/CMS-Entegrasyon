<?php

class EticUI
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

	

	public function displayCustomerOrder($tr)
	{
		$currency = new Currency($tr->id_currency);
		$t = '
		<div class="eticsoft">
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
							<td>' . $this->l('POS answer:') . '#' . $tr->result_code . ' ' . $tr->result_message . '<br/>
								' . $this->l('Date') . ' <span class="badge">' . $tr->date_update . '</span></td>
							<td><img src="' . $this->url . '/img/gateways/' . $tr->gateway . '.png" /></td>
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

	public function displayPdf($tr)
	{
		$currency = new Currency($tr->id_currency);
		$t = '
		<div class="eticsoft">
			<div class="col-md-6" >
				<div class="panel">
					<div class="panel-heading">
						<i class="icon-credit-card"></i>
						' . $this->l('Credit Card Process Details') . '
							' . $tr->gateway . '#' . $tr->boid . '
					</div>
					<table class="table">
						<tr>
							<td>' . $this->l('POS answer:') . '#' . $tr->result_code . ' ' . $tr->result_message . '<br/>
								' . $this->l('Date') . ' <span">' . $tr->date_update . '</span></td>
							<td>' . $tr->gateway . '</td>
						</tr>
						<tr>
							<td>' . $this->l('Total Amount') . '</td>
							<td><span>' . $this->displayPrice($tr->total_pay, $currency) . '</span></td>
						</tr>
						<tr>
							<td colspan="2">
								' . $this->l('IP Address') . ' <span">' . $tr->cip . '</span> 
								' . $this->l('Transaction number') . ' <span>' . $tr->boid . '</span>
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
								' . $tr->cc_name . '
						</div>
						<table class="table">
							<tr>
								<td>' . $this->l('Card Type') . '</td>
								<td>'.($tr->family != '' ? $tr->family : 'Credit/Debit').'</td>
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
}
