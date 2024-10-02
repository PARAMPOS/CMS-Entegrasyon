<?php
class ControllerExtensionPaymentParam extends Controller {

	private $error = array();

	public function index() {
		
		$this->load->language('extension/payment/param');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('payment_param', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
 
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_client_code'])) {
			$data['error_client_code'] = $this->error['error_client_code'];
		} else {
			$data['error_client_code'] = '';
		}

		if (isset($this->error['error_client_username'])) {
			$data['error_client_username'] = $this->error['error_client_username'];
		} else {
			$data['error_client_username'] = '';
		}

		if (isset($this->error['error_client_password'])) {
			$data['error_client_password'] = $this->error['error_client_password'];
		} else {
			$data['error_client_password'] = '';
		}

		if (isset($this->error['error_guid'])) {
			$data['error_guid'] = $this->error['error_guid'];
		} else {
			$data['error_guid'] = '';
		}

		if (isset($this->error['payment_type'])) {
			$data['error_payment_type'] = $this->error['payment_type'];
		} else {
			$data['error_payment_type'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/param', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/param', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->request->post['payment_param_payment_gateway'])) {
			$data['payment_param_payment_gateway'] = $this->request->post['payment_param_payment_gateway'];
		} else {
			$data['payment_param_payment_gateway'] = $this->config->get('payment_param_payment_gateway');
		}

		if (isset($this->request->post['payment_param_paymode'])) {
			$data['payment_param_paymode'] = $this->request->post['payment_param_paymode'];
		} else {
			$data['payment_param_paymode'] = $this->config->get('payment_param_paymode');
		}

		if (isset($this->request->post['payment_param_test'])) {
			$data['payment_param_test'] = $this->request->post['payment_param_test'];
		} else {
			$data['payment_param_test'] = $this->config->get('payment_param_test');
		}

		if (isset($this->request->post['payment_param_payment_type'])) {
			$data['payment_param_payment_type'] = $this->request->post['payment_param_payment_type'];
		} else {
			$data['payment_param_payment_type'] = $this->config->get('payment_param_payment_type');
		}

		if (isset($this->request->post['payment_param_transaction'])) {
			$data['payment_param_transaction'] = $this->request->post['payment_param_transaction'];
		} else {
			$data['payment_param_transaction'] = $this->config->get('payment_param_transaction');
		}

		if (isset($this->request->post['payment_param_standard_geo_zone_id'])) {
			$data['payment_param_standard_geo_zone_id'] = $this->request->post['payment_param_standard_geo_zone_id'];
		} else {
			$data['payment_param_standard_geo_zone_id'] = $this->config->get('payment_param_standard_geo_zone_id');
		}

		if (isset($this->request->post['payment_param_order_status_id'])) {
			$data['payment_param_order_status_id'] = $this->request->post['payment_param_order_status_id'];
		} else {
			$data['payment_param_order_status_id'] = $this->config->get('payment_param_order_status_id');
		}

		if (isset($this->request->post['payment_param_order_status_refunded_id'])) {
			$data['payment_param_order_status_refunded_id'] = $this->request->post['payment_param_order_status_refunded_id'];
		} else {
			$data['payment_param_order_status_refunded_id'] = $this->config->get('payment_param_order_status_refunded_id');
		}

		if (isset($this->request->post['payment_param_order_status_auth_id'])) {
			$data['payment_param_order_status_auth_id'] = $this->request->post['payment_param_order_status_auth_id'];
		} else {
			$data['payment_param_order_status_auth_id'] = $this->config->get('payment_param_order_status_auth_id');
		}

		if (isset($this->request->post['payment_param_order_status_fraud_id'])) {
			$data['payment_param_order_status_fraud_id'] = $this->request->post['payment_param_order_status_fraud_id'];
		} else {
			$data['payment_param_order_status_fraud_id'] = $this->config->get('payment_param_order_status_fraud_id');
		}

		if (isset($this->request->post['payment_param_transaction_method'])) {
			$data['payment_param_transaction_method'] = $this->request->post['payment_param_transaction_method'];
		} else {
			$data['payment_param_transaction_method'] = $this->config->get('payment_param_transaction_method');
		}

		if (isset($this->request->post['payment_param_client_code'])) {
			$data['payment_param_client_code'] = $this->request->post['payment_param_client_code'];
		} else {
			$data['payment_param_client_code'] = $this->config->get('payment_param_client_code');
		}

		if (isset($this->request->post['payment_param_client_username'])) {
			$data['payment_param_client_username'] = $this->request->post['payment_param_client_username'];
		} else {
			$data['payment_param_client_username'] = $this->config->get('payment_param_client_username');
		}

		if (isset($this->request->post['payment_param_client_password'])) {
			$data['payment_param_client_password'] = $this->request->post['payment_param_client_password'];
		} else {
			$data['payment_param_client_password'] = $this->config->get('payment_param_client_password');
		}

		if (isset($this->request->post['payment_param_guid'])) {
			$data['payment_param_guid'] = $this->request->post['payment_param_guid'];
		} else {
			$data['payment_param_guid'] = $this->config->get('payment_param_guid');
		}

		if (isset($this->request->post['payment_param_status'])) {
			$data['payment_param_status'] = $this->request->post['payment_param_status'];
		} else {
			$data['payment_param_status'] = $this->config->get('payment_param_status');
		}

		if (isset($this->request->post['payment_param_installment_status'])) {
			$data['payment_param_installment_status'] = $this->request->post['payment_param_installment_status'];
		} else {
			$data['payment_param_installment_status'] = $this->config->get('payment_param_installment_status');
		}

		if (isset($this->request->post['payment_param_sort_order'])) {
			$data['payment_param_sort_order'] = $this->request->post['payment_param_sort_order'];
		} else {
			$data['payment_param_sort_order'] = $this->config->get('payment_param_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/param', $data));
	}

	public function install() {
		$this->load->model('extension/payment/param');
		$this->model_extension_payment_param->install();
	}

	public function uninstall() {
		$this->load->model('extension/payment/param');
		$this->model_extension_payment_param->uninstall();
	}

	// Legacy 2.0.0
	public function orderAction() {
		return $this->order();
	}

	// Legacy 2.0.3
	public function action() {
		return $this->order();
	}

	public function order() {
		if ($this->config->get('payment_param_status')) {
			$this->load->model('extension/payment/param');

			$param_order = $this->model_extension_payment_param->getOrder($this->request->get['order_id']);

			if (!empty($param_order)) {
				$this->load->language('extension/payment/param');

				$param_order['total'] = $param_order['amount'];
				$param_order['total_formatted'] = $this->currency->format($param_order['amount'], $param_order['currency_code'], 1, true);

				$param_order['total_captured'] = $this->model_extension_payment_param->getTotalCaptured($param_order['param_order_id']);
				$param_order['total_captured_formatted'] = $this->currency->format($param_order['total_captured'], $param_order['currency_code'], 1, true);

				$param_order['uncaptured'] = $param_order['total'] - $param_order['total_captured'];

				$param_order['total_refunded'] = $this->model_extension_payment_param->getTotalRefunded($param_order['param_order_id']);
				$param_order['total_refunded_formatted'] = $this->currency->format($param_order['total_refunded'], $param_order['currency_code'], 1, true);

				$param_order['unrefunded'] = $param_order['total_captured'] - $param_order['total_refunded'];

				$data['text_payment_info'] = $this->language->get('text_payment_info');
				$data['text_order_total'] = $this->language->get('text_order_total');
				$data['text_void_status'] = $this->language->get('text_void_status');
				$data['text_transactions'] = $this->language->get('text_transactions');
				$data['text_column_amount'] = $this->language->get('text_column_amount');
				$data['text_column_type'] = $this->language->get('text_column_type');
				$data['text_column_created'] = $this->language->get('text_column_created');
				$data['text_column_transactionid'] = $this->language->get('text_column_transactionid');
				$data['btn_refund'] = $this->language->get('btn_refund');
				$data['btn_capture'] = $this->language->get('btn_capture');
				$data['text_confirm_refund'] = $this->language->get('text_confirm_refund');
				$data['text_confirm_capture'] = $this->language->get('text_confirm_capture');

				$data['text_total_captured'] = $this->language->get('text_total_captured');
				$data['text_total_refunded'] = $this->language->get('text_total_refunded');
				$data['text_capture_status'] = $this->language->get('text_capture_status');
				$data['text_refund_status'] = $this->language->get('text_refund_status');

				$data['text_empty_refund'] = $this->language->get('text_empty_refund');
				$data['text_empty_capture'] = $this->language->get('text_empty_capture');

				$data['param_order'] = $param_order;
				$data['user_token'] = $this->session->data['user_token'];
				$data['order_id'] = (int)$this->request->get['order_id'];

				return $this->load->view('extension/payment/param_order', $data);
			}
		}
	}

	public function refund() {
		$this->load->language('extension/payment/param');

		$order_id = $this->request->post['order_id'];
		$refund_amount = (double)$this->request->post['refund_amount'];

		if ($order_id && $refund_amount > 0) {
			$this->load->model('extension/payment/param');
			$result = $this->model_extension_payment_param->refund($order_id, $refund_amount);

			// Check if any error returns
			if (isset($result->Errors) || $result === false) {
				$json['error'] = true;
				$reason = '';
				if ($result === false) {
					$reason = $this->language->get('text_unknown_failure');
				} else {
					$errors = explode(',', $result->Errors);
					foreach ($errors as $error) {
						$reason .= $this->language->get('text_card_message_' . $result->Errors);
					}
				}
				$json['message'] = $this->language->get('text_refund_failed') . $reason;
			} else {
				$param_order = $this->model_extension_payment_param->getOrder($order_id);
				$this->model_extension_payment_param->addTransaction($param_order['param_order_id'], $result->Refund->TransactionID, 'refund', $result->Refund->TotalAmount / 100, $param_order['currency_code']);

				$total_captured = $this->model_extension_payment_param->getTotalCaptured($param_order['param_order_id']);
				$total_refunded = $this->model_extension_payment_param->getTotalRefunded($param_order['param_order_id']);
				$refund_status = 0;

				if ($total_captured == $total_refunded) {
					$refund_status = 1;
					$this->model_extension_payment_param->updateRefundStatus($param_order['param_order_id'], $refund_status);
				}

				$json['data'] = array();
				$json['data']['transactionid'] = $result->TransactionID;
				$json['data']['created'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = number_format($refund_amount, 2, '.', '');
				$json['data']['total_refunded_formatted'] = $this->currency->format($total_refunded, $param_order['currency_code'], 1, true);
				$json['data']['refund_status'] = $refund_status;
				$json['data']['remaining'] = $total_captured - $total_refunded;
				$json['message'] = $this->language->get('text_refund_success');
				$json['error'] = false;
			}
		} else {
			$json['error'] = true;
			$json['message'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function capture() {
		$this->load->language('extension/payment/param');

		$order_id = $this->request->post['order_id'];
		$capture_amount = (double)$this->request->post['capture_amount'];

		if ($order_id && $capture_amount > 0) {
			$this->load->model('extension/payment/param');
			$param_order = $this->model_extension_payment_param->getOrder($order_id);
			$result = $this->model_extension_payment_param->capture($order_id, $capture_amount, $param_order['currency_code']);

			// Check if any error returns
			if (isset($result->Errors) || $result === false) {
				$json['error'] = true;
				$reason = '';
				if ($result === false) {
					$reason = $this->language->get('text_unknown_failure');
				} else {
					$errors = explode(',', $result->Errors);
					foreach ($errors as $error) {
						$reason .= $this->language->get('text_card_message_' . $result->Errors);
					}
				}
				$json['message'] = $this->language->get('text_capture_failed') . $reason;
			} else {
				$this->model_extension_payment_param->addTransaction($param_order['param_order_id'], $result->TransactionID, 'payment', $capture_amount, $param_order['currency_code']);

				$total_captured = $this->model_extension_payment_param->getTotalCaptured($param_order['param_order_id']);
				$total_refunded = $this->model_extension_payment_param->getTotalRefunded($param_order['param_order_id']);

				$remaining = $param_order['amount'] - $capture_amount;
				if ($remaining <= 0) {
					$remaining = 0;
				}

				$this->model_extension_payment_param->updateCaptureStatus($param_order['param_order_id'], 1);
				$this->model_extension_payment_param->updateTransactionId($param_order['param_order_id'], $result->TransactionID);

				$json['data'] = array();
				$json['data']['transactionid'] = $result->TransactionID;
				$json['data']['created'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = number_format($capture_amount, 2, '.', '');
				$json['data']['total_captured_formatted'] = $this->currency->format($total_captured, $param_order['currency_code'], 1, true);
				$json['data']['capture_status'] = 1;
				$json['data']['remaining'] = $remaining;
				$json['message'] = $this->language->get('text_capture_success');
				$json['error'] = false;
			}
		} else {
			$json['error'] = true;
			$json['message'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/param')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!isset($this->request->post['payment_param_client_code'])) {
			$this->error['error_client_code'] = $this->language->get('error_client_code');
		}
		if (!isset($this->request->post['payment_param_client_username'])) {
			$this->error['error_client_username'] = $this->language->get('error_client_username');
		}
		if (!isset($this->request->post['payment_param_client_password'])) {
			$this->error['error_client_password'] = $this->language->get('error_client_password');
		}
		if (!isset($this->request->post['payment_param_guid'])) {
			$this->error['error_guid'] = $this->language->get('error_guid');
		}
		return !$this->error;
	}

}
