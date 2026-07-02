<?php
class ControllerExtensionPaymentVindiboleto extends Controller {
	private $error = array();

	public function index() {
		$this->vindi = new VindiApi($this->registry);
		$this->load->language('extension/payment/vindiboleto');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_vindiboleto', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}
		
		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_help'] = $this->language->get('tab_help');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_sandbox'] = $this->language->get('text_sandbox');
		$data['text_production'] = $this->language->get('text_production');

		$data['entry_order_status_pen'] = $this->language->get('entry_order_status_pen');
		$data['entry_order_status_can'] = $this->language->get('entry_order_status_can');
		$data['entry_order_status_apr'] = $this->language->get('entry_order_status_apr');
		$data['entry_order_status_con'] = $this->language->get('entry_order_status_con');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_type'] = $this->language->get('entry_type');
		$data['entry_token'] = $this->language->get('entry_token');
		$data['entry_days'] = $this->language->get('entry_days');
		$data['entry_doc'] = $this->language->get('entry_doc');
		$data['entry_doc2'] = $this->language->get('entry_doc2');
		$data['entry_raz'] = $this->language->get('entry_raz');
		$data['entry_number'] = $this->language->get('entry_number');
		$data['entry_complement'] = $this->language->get('entry_complement');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		$data['murl'] = 'https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=42087';
		$data['module_name'] = "Vindi Pagamentos";

		$data['atual'] = $this->vindi->checkUpdate();
		$data['version'] = $this->vindi->moduleVersion();

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = '';
		}
		
		if (isset($this->error['token'])) {
			$data['error_token'] = $this->error['token'];
		} else {
			$data['error_token'] = '';
		}
		
		if (isset($this->error['doc'])) {
			$data['error_doc'] = $this->error['doc'];
		} else {
			$data['error_doc'] = '';
		}
		
		if (isset($this->error['number'])) {
			$data['error_number'] = $this->error['number'];
		} else {
			$data['error_number'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/vindiboleto', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/vindiboleto', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
		
		if (isset($this->request->post['payment_vindiboleto_title'])) {
			$data['payment_vindiboleto_title'] = $this->request->post['payment_vindiboleto_title'];
		} else {
			$data['payment_vindiboleto_title'] = $this->config->get('payment_vindiboleto_title');
		}

		if (isset($this->request->post['payment_vindiboleto_token'])) {
			$data['payment_vindiboleto_token'] = $this->request->post['payment_vindiboleto_token'];
		} else {
			$data['payment_vindiboleto_token'] = $this->config->get('payment_vindiboleto_token');
		}
		
		if (isset($this->request->post['payment_vindiboleto_days'])) {
			$data['payment_vindiboleto_days'] = $this->request->post['payment_vindiboleto_days'];
		} else {
			$data['payment_vindiboleto_days'] = $this->config->get('payment_vindiboleto_days');
		}
		
		if (isset($this->request->post['payment_vindiboleto_doc'])) {
			$data['payment_vindiboleto_doc'] = $this->request->post['payment_vindiboleto_doc'];
		} else {
			$data['payment_vindiboleto_doc'] = $this->config->get('payment_vindiboleto_doc');
		}
		
		if (isset($this->request->post['payment_vindiboleto_doc2'])) {
			$data['payment_vindiboleto_doc2'] = $this->request->post['payment_vindiboleto_doc2'];
		} else {
			$data['payment_vindiboleto_doc2'] = $this->config->get('payment_vindiboleto_doc2');
		}
		
		if (isset($this->request->post['payment_vindiboleto_raz'])) {
			$data['payment_vindiboleto_raz'] = $this->request->post['payment_vindiboleto_raz'];
		} else {
			$data['payment_vindiboleto_raz'] = $this->config->get('payment_vindiboleto_raz');
		}

		if (isset($this->request->post['payment_vindiboleto_number'])) {
			$data['payment_vindiboleto_number'] = $this->request->post['payment_vindiboleto_number'];
		} else {
			$data['payment_vindiboleto_number'] = $this->config->get('payment_vindiboleto_number');
		}
		
		if (isset($this->request->post['payment_vindiboleto_complement'])) {
			$data['payment_vindiboleto_complement'] = $this->request->post['payment_vindiboleto_complement'];
		} else {
			$data['payment_vindiboleto_complement'] = $this->config->get('payment_vindiboleto_complement');
		}
		
		if (isset($this->request->post['payment_vindiboleto_total'])) {
			$data['payment_vindiboleto_total'] = $this->request->post['payment_vindiboleto_total'];
		} elseif($this->config->has('payment_vindiboleto_total')) {
			$data['payment_vindiboleto_total'] = $this->config->get('payment_vindiboleto_total');
		} else {
			$data['payment_vindiboleto_total'] = 5.00;
		}

		if (isset($this->request->post['payment_vindiboleto_order_status_id'])) {
			$data['payment_vindiboleto_order_status_id'] = $this->request->post['payment_vindiboleto_order_status_id'];
		} else {
			$data['payment_vindiboleto_order_status_id'] = $this->config->get('payment_vindiboleto_order_status_id');
		}
		
		if (isset($this->request->post['payment_vindiboleto_order_status_id1'])) {
			$data['payment_vindiboleto_order_status_id1'] = $this->request->post['payment_vindiboleto_order_status_id1'];
		} else {
			$data['payment_vindiboleto_order_status_id1'] = $this->config->get('payment_vindiboleto_order_status_id1');
		}
		
		if (isset($this->request->post['payment_vindiboleto_order_status_id2'])) {
			$data['payment_vindiboleto_order_status_id2'] = $this->request->post['payment_vindiboleto_order_status_id2'];
		} else {
			$data['payment_vindiboleto_order_status_id2'] = $this->config->get('payment_vindiboleto_order_status_id2');
		}
		
		if (isset($this->request->post['payment_vindiboleto_order_status_id3'])) {
			$data['payment_vindiboleto_order_status_id3'] = $this->request->post['payment_vindiboleto_order_status_id3'];
		} else {
			$data['payment_vindiboleto_order_status_id3'] = $this->config->get('payment_vindiboleto_order_status_id3');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_vindiboleto_geo_zone_id'])) {
			$data['payment_vindiboleto_geo_zone_id'] = $this->request->post['payment_vindiboleto_geo_zone_id'];
		} else {
			$data['payment_vindiboleto_geo_zone_id'] = $this->config->get('payment_vindiboleto_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_vindiboleto_status'])) {
			$data['payment_vindiboleto_status'] = $this->request->post['payment_vindiboleto_status'];
		} else {
			$data['payment_vindiboleto_status'] = $this->config->get('payment_vindiboleto_status');
		}

		if (isset($this->request->post['payment_vindiboleto_sort_order'])) {
			$data['payment_vindiboleto_sort_order'] = $this->request->post['payment_vindiboleto_sort_order'];
		} else {
			$data['payment_vindiboleto_sort_order'] = $this->config->get('payment_vindiboleto_sort_order');
		}
		
		$this->load->model('customer/custom_field');
		
        $data['custom_fields'] = $this->model_customer_custom_field->getCustomFields();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/vindiboleto', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/vindiboleto')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!isset($this->request->post['payment_vindiboleto_token']) || $this->request->post['payment_vindiboleto_token'] == '' ) {
			$this->error['token'] = $this->language->get('error_token');
		}
		
		if (!isset($this->request->post['payment_vindiboleto_doc']) || $this->request->post['payment_vindiboleto_doc'] == '' ) {
			$this->error['doc'] = $this->language->get('error_doc');
		}
		
		if (!isset($this->request->post['payment_vindiboleto_title']) || $this->request->post['payment_vindiboleto_title'] == '' ) {
			$this->error['title'] = $this->language->get('error_title');
		}
		
		if (!isset($this->request->post['payment_vindiboleto_number']) || $this->request->post['payment_vindiboleto_number'] == '' ) {
			$this->error['number'] = $this->language->get('error_number');
		}

		return !$this->error;
	}
}