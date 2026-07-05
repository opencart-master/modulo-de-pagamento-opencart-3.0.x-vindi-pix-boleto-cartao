<?php
class ControllerExtensionPaymentVindiboleto extends Controller {
	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['text_loading'] = $this->language->get('text_loading');

		$data['continue'] = $this->url->link('checkout/success');

		return $this->load->view('extension/payment/vindiboleto', $data);
	}

	public function confirm() {
	    $json = array(); 
		if ($this->session->data['payment_method']['code'] == 'vindiboleto') {
			$this->vindi = new VindiApi($this->registry);
			
			$this->load->model('checkout/order');
			
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			$telephone = preg_replace("/[^0-9]/", "", $order_info['telephone']);
			if(strlen($telephone) >= 11) {
			$tipocontato = 'M';
			} else {
			$tipocontato = 'H'; 
			}
			$campos = $order_info['custom_field'];
			if (!empty($order_info['payment_custom_field'][$this->config->get('payment_vindiboleto_complement')])) {
			$complement = $order_info['payment_custom_field'][$this->config->get('payment_vindiboleto_complement')];
			} else {
			$complement = '';	
			}
			if (!empty($order_info['shipping_custom_field'][$this->config->get('payment_vindiboleto_complement')])) {
			$complement2 = $order_info['shipping_custom_field'][$this->config->get('payment_vindiboleto_complement')];  
			} else {
			$complement2 = '';  	
			}
			$val["token_account"]  = $this->config->get('payment_vindiboleto_token');
			$val["customer"]["contacts"][1]["type_contact"] = $tipocontato;
			$val["customer"]["contacts"][1]["number_contact"] = $telephone;
			
			if ($this->cart->hasShipping()) {
			$val["customer"]["addresses"][0]["type_address"] = "D";
			$val["customer"]["addresses"][0]["postal_code"] = preg_replace("/[^0-9]/", "", $order_info['shipping_postcode']);
			$val["customer"]["addresses"][0]["street"] = $order_info['shipping_address_1'];
			$val["customer"]["addresses"][0]["number"] = $order_info['shipping_custom_field'][$this->config->get('payment_vindiboleto_number')];
			$val["customer"]["addresses"][0]["completion"] = $complement2;	
			$val["customer"]["addresses"][0]["neighborhood"] = $order_info['shipping_address_2'];
			$val["customer"]["addresses"][0]["city"] = $order_info['shipping_city'];
			$val["customer"]["addresses"][0]["state"] = $order_info['shipping_zone_code'];         
			}
			$val["customer"]["addresses"][1]["type_address"] = "B";
			$val["customer"]["addresses"][1]["postal_code"] = preg_replace("/[^0-9]/", "", $order_info['payment_postcode']);
			$val["customer"]["addresses"][1]["street"] = $order_info['payment_address_1'];
			$val["customer"]["addresses"][1]["number"] = $order_info['payment_custom_field'][$this->config->get('payment_vindiboleto_number')];
			$val["customer"]["addresses"][1]["completion"] = $complement;
			$val["customer"]["addresses"][1]["neighborhood"] = $order_info['payment_address_2'];
			$val["customer"]["addresses"][1]["city"] = $order_info['payment_city'];
			$val["customer"]["addresses"][1]["state"] = $order_info['payment_zone_code'];
			$val["customer"]["name"] = $order_info['firstname']. ' '. $order_info['lastname'];
			if (!empty($campos[$this->config->get('payment_vindiboleto_doc2')]) && $this->config->get('payment_vindiboleto_doc2') > 0 ) {
			$doc2 = preg_replace("/[^0-9]/", "", $campos[$this->config->get('payment_vindiboleto_doc2')]);
			$val["customer"]["cnpj"] = $doc2;
			$val["customer"]["company_name"] = $campos[$this->config->get('payment_vindiboleto_raz')];
			$val["customer"]["trade_name"] =  $campos[$this->config->get('payment_vindiboleto_raz')];
			} 
			if (!empty($campos[$this->config->get('payment_vindiboleto_doc')])) {
			$doc = preg_replace("/[^0-9]/", "", $campos[$this->config->get('payment_vindiboleto_doc')]);
			$val["customer"]["cpf"] = $doc;
			} else {
			$val["customer"]["cpf"] = " ";    
			}
			$val["customer"]["email"] = $order_info['email'];
			foreach ($this->cart->getProducts() as $key => $product) {
			$val["transaction_product"][$key]["description"] = $product['name'];
			$val["transaction_product"][$key]["quantity"] = $product['quantity'];
			$val["transaction_product"][$key]["price_unit"] = number_format($product['price'], 2, '.', '');
			}
			if ($this->cart->hasShipping()) {
			$val["transaction"]["shipping_type"] = $this->session->data['shipping_method']['title'];
			$val["transaction"]["shipping_price"] = number_format($this->session->data['shipping_method']['cost'], 2, '.', '');
			$precofrete = $this->session->data['shipping_method']['cost'];
			} else {
			$precofrete = 0;   
			}
			$precototal = $this->cart->getSubTotal();
			$desc = $precototal + $precofrete - $order_info['total'];
			if($desc > 0) {
			$val["transaction"]["price_discount"] = number_format($desc, 2, '.', '');
			}
			$val["transaction"]["url_notification"] = HTTPS_SERVER . 'index.php?route=extension/payment/vindiboleto/callback';
			$val["transaction"]["order_number"] = $this->session->data['order_id'];
			$val["transaction"]["customer_ip"] = $this->request->server['REMOTE_ADDR'];
			if($this->config->get('payment_vindiboleto_days') == '') {
			$num = 0;
			} else {
			$num = $this->config->get('payment_vindiboleto_days');	
			}
			$hoje = date('d-m-Y');
			$datavenc = date('d/m/Y', strtotime('+ '. $num .' days', strtotime($hoje)));

			$val["payment"]["payment_method_id"] = "6";
			$val["payment"]["billet_date_expiration"] = $datavenc;

			$resposta = $this->getBoleto($val);
			
			if ($this->vindi->sandbox()) {
				$this->log->write('DEV PAYLOAD' . json_encode($val));
				$this->log->write('DEV RESPONSE' . json_encode($resposta));
			}
			
			if($resposta['message_response']['message'] == 'success') {
			$comment  = "Situação: " . $resposta['data_response']['transaction']['status_name'] . "\n";
			$comment .= "ID: " . $resposta['data_response']['transaction']['transaction_id'] . "\n";
			$comment .= "Link do Boleto: <a href='" . $resposta['data_response']['transaction']['payment']['url_payment'] . "' class='label label-info' target='_blank'> VER BOLETO </a> \n\n";
			$comment .= "LINHA DIGITÁVEL::
			<div class='form-group col-sm-10 col-md-6'><div class='input-group'>
			<input type='text' name='input-copia' id='input-copia' value='" . preg_replace("/[^0-9]/", "", $resposta['data_response']['transaction']['payment']['linha_digitavel']) . "' class='form-control' />
			<span class='input-group-btn'><input type='button' value='Copiar' id='button-copiar' data-loading-text='Copiando'  class='btn btn-primary' /></span></div></div><br><br>
			";
			$json['success'] = "Success";
			$json['continue'] = $this->url->link('checkout/success');
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_vindiboleto_order_status_id'), $comment, $notify = true);
			} else {
			if (isset($resposta['error_response']['general_errors']) && !empty($resposta['error_response']['general_errors'])) {
			foreach ($resposta['error_response']['general_errors'] as $general_error){
			$codigo_erro = $general_error['code'];
			$descricao_erro = $general_error['message'];
			}
			}
				
			if (isset($resposta['error_response']['validation_errors']) && !empty($resposta['error_response']['validation_errors'])) {
			foreach ($resposta['error_response']['validation_errors'] as $validation_error) {
			$codigo_erro = $validation_error['field'];
			$descricao_erro = $validation_error['message_complete'];
			}
			}
				
			$codigo_erro1 = substr($codigo_erro, 0, - 3);
			$descricao_erro1 = substr($descricao_erro, 0, - 3);
				
			if ($codigo_erro1 == '') {
			$codigo_erro = '0000000';
			}
				
			if ($descricao_erro1 == '') {
			$descricao_erro = 'Erro no Processamento do Boleto';
			}
				
			$json['error'] = $descricao_erro;
				
			$this->log->write('ERRO API: Vindi Boleto - PEDIDO ID ' .$this->session->data['order_id']. ' - ' . json_encode($resposta));
			}

	    }
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getBoleto($json_convert) {
		$this->vindi = new VindiApi($this->registry);
    	return $this->vindi->createPayment($json_convert);   
	}
	
	public function callback() {
	    
	    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
	        
	        $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
			$this->response->addHeader('HTTP/1.1 200 OK');
	        
	        if (isset($this->request->post)) {
	        $oid = (int) $this->request->post['transaction']['order_number'];
	        $this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($oid);
			
			if ($order_info && $this->request->post['token_transaction'] && $this->request->post['transaction']['transaction_id'] && $order_info['payment_code'] == 'vindiboleto') {
			    
		        $order_status_ids = $order_info['order_status_id'];
				$order_status_id = $this->config->get('payment_vindiboleto_order_status_id');

				switch($this->request->post['transaction']['status_id']) {
					case '4':
						$order_status_id = $this->config->get('payment_vindiboleto_order_status_id');
						break;
					case '6':
						$order_status_id = $this->config->get('payment_vindiboleto_order_status_id2');
						break;
					case '7':
						$order_status_id = $this->config->get('payment_vindiboleto_order_status_id1');
						break;
					case '24':
						$order_status_id = $this->config->get('payment_vindiboleto_order_status_id3');
						break;
					case '87':
						$order_status_id = $this->config->get('payment_vindiboleto_order_status_id');
						break;
					case '89':
						$order_status_id = $this->config->get('payment_vindiboleto_order_status_id1');
						break;
				}
				
				$comment  = "Token: " . $this->request->post['transaction']['transaction_token'] . "\n";
		        $comment .= "Valor Pago: " . $this->request->post['transaction']['price_payment'] . "\n";
		        $comment .= "Situação: ". $this->request->post['transaction']['status_name'] ."\n";
		        $comment .= "Pago Com: "	. $this->request->post['transaction']['payment_method_name'];
                
                if ($order_status_ids != $order_status_id) {
                $this->model_checkout_order->addOrderHistory($oid, $order_status_id, $comment, $notify = true);
                }
			}

	        }

	    } else {
	        http_response_code(404);
	        $this->log->write('ERRO no Retorno: Vindi Boleto - IP '. $this->request->server['REMOTE_ADDR']);
	    }
		
	}
}