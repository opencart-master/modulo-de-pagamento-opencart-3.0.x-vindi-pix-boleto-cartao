<?php
class ControllerExtensionPaymentVindipix extends Controller {
	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['text_loading'] = $this->language->get('text_loading');

		$data['continue'] = $this->url->link('checkout/success');

		return $this->load->view('extension/payment/vindipix', $data);
	}

	public function confirm() {
	    $json = array(); 
		if ($this->session->data['payment_method']['code'] == 'vindipix') {
        include_once('vindipixcode.php');
	    }
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getPix($json_convert) {
	    
	if ($this->config->get('payment_vindipix_type') == 0) {
			$url = "https://api.intermediador.sandbox.yapay.com.br/api/v3/transactions/payment";    
			} else {
			$url = "https://api.intermediador.yapay.com.br/api/v3/transactions/payment";     
	}
	
	$header = array('Accept: application/json', 'Content-Type: application/json;charset=UTF-8', 'User-Agent: Aplicação Opencart Master');
	
	$soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $url);
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
    curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST,           true );
    curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $json_convert);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
    $response = curl_exec($soap_do); 
    curl_close($soap_do);
  
    return $response;   
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
			
			if ($order_info && $this->request->post['token_transaction'] && $this->request->post['transaction']['transaction_id'] && $order_info['payment_code'] == 'vindipix') {
			    
		        $order_status_ids = $order_info['order_status_id'];
				$order_status_id = $this->config->get('payment_vindipix_order_status_id');

				switch($this->request->post['transaction']['status_id']) {
					case '4':
						$order_status_id = $this->config->get('payment_vindipix_order_status_id');
						break;
					case '6':
						$order_status_id = $this->config->get('payment_vindipix_order_status_id2');
						break;
					case '7':
						$order_status_id = $this->config->get('payment_vindipix_order_status_id1');
						break;
					case '24':
						$order_status_id = $this->config->get('payment_vindipix_order_status_id3');
						break;
					case '87':
						$order_status_id = $this->config->get('payment_vindipix_order_status_id');
						break;
					case '89':
						$order_status_id = $this->config->get('payment_vindipix_order_status_id1');
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
	        $this->log->write('ERRO no Retorno: Yapay Pix - IP '. $this->request->server['REMOTE_ADDR']);
	    }
		
	}
}