<?php
class ControllerExtensionPaymentVindicartao extends Controller {
	public function index() {
		$this->vindi = new VindiApi($this->registry);
		$this->load->language('extension/payment/vindicartao');
		$data['text_loading'] = $this->language->get('text_loading');
		
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['text_loading'] = $this->language->get('text_loading');
		$data['text_credit_card'] = $this->language->get('text_credit_card');
		$data['entry_cc_type'] = $this->language->get('entry_cc_type');
		$data['entry_cc_number'] = $this->language->get('entry_cc_number');
		$data['entry_cc_date'] = $this->language->get('entry_cc_date');
		$data['entry_cc_mes'] = $this->language->get('entry_cc_mes');
		$data['entry_cc_ano'] = $this->language->get('entry_cc_ano');
		$data['entry_cc_cvv'] = $this->language->get('entry_cc_cvv');
		$data['entry_cc_doc'] = $this->language->get('entry_cc_doc');
		$data['entry_cc_name'] = $this->language->get('entry_cc_name');
		$data['entry_cc_mes'] = $this->language->get('entry_cc_mes');
		$data['entry_cc_parc'] = $this->language->get('entry_cc_parc');
		
		if ($this->vindi->sandbox()) {
		   $data['sec'] = 'f';
		} else {
		   $data['sec'] = 'v'; 
		}
 
		$data['continue'] = $this->url->link('checkout/success');
		
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$campos = $order_info['custom_field'];
		$ttotal = $order_info['total'];
		$doc = preg_replace("/[^0-9]/", "", $campos[$this->config->get('payment_vindicartao_doc')]);
		$data['doc'] = preg_replace("/[^0-9]/", "", $doc);
		
		$data['cards'] = array();
		
		$data['cards'][] = array(
			'text'  => 'Selecione a Bandeira',
			'value' => ''
		);

		$data['cards'][] = array(
			'text'  => 'Visa',
			'value' => '3'
		);

		$data['cards'][] = array(
			'text'  => 'MasterCard',
			'value' => '4'
		);

		$data['cards'][] = array(
			'text'  => 'Elo',
			'value' => '16'
		);

		$data['cards'][] = array(
			'text'  => 'American Express',
			'value' => '5'
		);

		$data['cards'][] = array(
			'text'  => 'Hipercard',
			'value' => '20'
		);
		
		
		$data['cards'][] = array(
			'text'  => 'Aura',
			'value' => '18'
		);
		
		$data['cards'][] = array(
			'text'  => 'Hiper (Itaú)',
			'value' => '25'
		);
		
		$data['cards'][] = array(
			'text'  => 'JBC',
			'value' => '19'
		);
		
		$data['cards'][] = array(
			'text'  => 'Discover',
			'value' => '15'
		);
		
		$data['months'] = array();
		
		$data['months'][] = array(
			'text'  => 'Selecione o Mês',
			'value' => ''
		);
       
		for ($i = 1; $i <= 12; $i++) {
		    setlocale(LC_TIME, 'pt_BR.utf-8');
			$data['months'][] = array(
				'text'  => ucwords(strftime('%B', mktime(0, 0, 0, $i, 1, 2000))),
				'value' => sprintf('%02d', $i)
			);
		}

		$today = getdate();

		$data['year_valids'] = array();
		
		$data['year_valids'][] = array(
			'text'  => 'Selecione o Ano',
			'value' => ''
		);

		for ($i = $today['year']; $i < $today['year'] + 12; $i++) {
			$data['year_valids'][] = array(
				'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
				'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
			);
		}
		
		return $this->load->view('extension/payment/vindicartao', $data);
	}

	public function confirm() {
		
	    $this->load->language('extension/payment/vindicartao');
	    $json = array(); 
		if ($this->session->data['payment_method']['code'] == 'vindicartao') {
			$this->vindi = new VindiApi($this->registry);
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
					
			$bandeira = $this->request->post['cc_bandeira'];
			$parcela = $this->request->post['cc_parc'];
			$cartao = $this->request->post['cc_number'];
			$mes = $this->request->post['cc_data'];
			$ano = $this->request->post['cc_data1'];
			$cvv = $this->request->post['cc_cvv'];
			$name = $this->request->post['cc_name'];
			$cpf = preg_replace('/[^0-9]/', '', $this->request->post['cc_doc']);
			$finger_print = $this->request->post['finger_print'];


		if ((utf8_strlen($this->request->post['cc_number']) < 15)) {
			$json['error']['cc_number'] = $this->language->get('error_number');
			$json['val']['cc_number'] = 'cc_number';
		} elseif (!$this->validaCRT($cartao)) {
			$json['error']['cc_number'] = $this->language->get('error_number2');
			$json['val']['cc_number'] = 'cc_number';
		} else {
			$json['val']['cc_number'] = 'cc_number';
		}

		if ((utf8_strlen($this->request->post['cc_cvv']) < 3)) {
			$json['error']['cc_cvv'] = $this->language->get('error_cvv');
			$json['val']['cc_cvv'] = 'cc_cvv';
		} else {
			$json['val']['cc_cvv'] = 'cc_cvv';
		}

		if ((utf8_strlen($this->request->post['cc_doc']) < 11)) {
			$json['error']['cc_doc'] = $this->language->get('error_doc');
			$json['val']['cc_doc'] = 'cc_doc';
		} elseif (!$this->validaCPF($cpf)) {
			$json['error']['cc_doc'] = $this->language->get('error_doc2');
			$json['val']['cc_doc'] = 'cc_doc';
		} else {
			$json['val']['cc_doc'] = 'cc_doc';
		}

		if ((utf8_strlen($this->request->post['cc_name']) < 5)) {
			$json['error']['cc_name'] = $this->language->get('error_name');
			$json['val']['cc_name'] = 'cc_name';
		} else {
			$json['val']['cc_name'] = 'cc_name';
		}

		if ($this->request->post['cc_data'] == '') {
			$json['error']['cc_data'] = $this->language->get('error_mes');
			$json['val']['cc_data'] = 'cc_data';
		} else {
			$json['val']['cc_data'] = 'cc_data';
		}

		if ($this->request->post['cc_parc'] =='') {
		$json['error']['cc_parc'] = $this->language->get('error_parc');
		$json['val']['cc_parc'] = 'cc_parc';  
		} else {
		$json['val']['cc_parc'] = 'cc_parc';
		}
				
		if ($this->request->post['cc_bandeira'] =='') {
		$json['error']['cc_bandeira'] = $this->language->get('error_band');
		$json['val']['cc_bandeira'] = 'cc_bandeira';  
		} else {
		$json['val']['cc_bandeira'] = 'cc_bandeira';
		}	

		if ($this->request->post['cc_data1'] == '') {
			$json['error']['cc_data1'] = $this->language->get('error_ano');
			$json['val']['cc_data1'] = 'cc_data1';
		} else {
			$json['val']['cc_data1'] = 'cc_data1';
		}
			
		} else {
				
			$bandeira = '';
			$parcela = '';
			$cartao = '';
			$mes = '';
			$ano = '';
			$cvv = '';
			$name = '';
			$cpf = '';	
			$finger_print = '';	
				
		}
	
	
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$telephone = preg_replace("/[^0-9]/", "", $order_info['telephone']);
		if(strlen($telephone) >= 11) {
		$tipocontato = 'M';
		} else {
		$tipocontato = 'H'; 
		}
		$campos = $order_info['custom_field'];
		if (!empty($order_info['payment_custom_field'][$this->config->get('payment_vindicartao_complement')])) {
		$complement = $order_info['payment_custom_field'][$this->config->get('payment_vindicartao_complement')];
		} else {
		$complement = '';	
		}
		if (!empty($order_info['shipping_custom_field'][$this->config->get('payment_vindicartao_complement')])) {
		$complement2 = $order_info['shipping_custom_field'][$this->config->get('payment_vindicartao_complement')];  
		} else {
		$complement2 = '';  	
		}
		$val["token_account"]  = $this->config->get('payment_vindicartao_token');
		$val["customer"]["contacts"][1]["type_contact"] = $tipocontato;
		$val["customer"]["contacts"][1]["number_contact"] = $telephone;

		if ($this->cart->hasShipping()) {
		$val["customer"]["addresses"][0]["type_address"] = "D";
		$val["customer"]["addresses"][0]["postal_code"] = preg_replace("/[^0-9]/", "", $order_info['shipping_postcode']);
		$val["customer"]["addresses"][0]["street"] = $order_info['shipping_address_1'];
		$val["customer"]["addresses"][0]["number"] = $order_info['shipping_custom_field'][$this->config->get('payment_vindicartao_number')];
		$val["customer"]["addresses"][0]["completion"] = $complement2;	
		$val["customer"]["addresses"][0]["neighborhood"] = $order_info['shipping_address_2'];
		$val["customer"]["addresses"][0]["city"] = $order_info['shipping_city'];
		$val["customer"]["addresses"][0]["state"] = $order_info['shipping_zone_code'];  
		}
		$val["customer"]["addresses"][1]["type_address"] = "B";
		$val["customer"]["addresses"][1]["postal_code"] = preg_replace("/[^0-9]/", "", $order_info['payment_postcode']);
		$val["customer"]["addresses"][1]["street"] = $order_info['payment_address_1'];
		$val["customer"]["addresses"][1]["number"] = $order_info['payment_custom_field'][$this->config->get('payment_vindicartao_number')];
		$val["customer"]["addresses"][1]["completion"] = $complement;
		$val["customer"]["addresses"][1]["neighborhood"] = $order_info['payment_address_2'];
		$val["customer"]["addresses"][1]["city"] = $order_info['payment_city'];
		$val["customer"]["addresses"][1]["state"] = $order_info['payment_zone_code'];
		$val["customer"]["name"] =  $order_info['firstname']. ' '. $order_info['lastname'];
		if (!empty($campos[$this->config->get('payment_vindicartao_doc2')]) && $this->config->get('payment_vindicartao_doc2') > 0 ) {
		$doc2 = preg_replace("/[^0-9]/", "", $campos[$this->config->get('payment_vindicartao_doc2')]);
		$val["customer"]["cnpj"] = $doc2;
		$val["customer"]["company_name"] = $campos[$this->config->get('payment_vindicartao_raz')];
		$val["customer"]["trade_name"] =  $campos[$this->config->get('payment_vindicartao_raz')];
		}
		$val["customer"]["cpf"] = preg_replace("/[^0-9]/", "", $cpf);
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
		$val["transaction"]["url_notification"] = HTTPS_SERVER . 'index.php?route=extension/payment/vindicartao/callback';
		$val["transaction"]["order_number"] = $this->session->data['order_id'];
		$val["transaction"]["customer_ip"] = $this->request->server['REMOTE_ADDR'];
		$val["transaction"]["available_payment_methods"] = "2,3,4,5,6,7,14,15,16,18,19,21,22,23";
		$val["payment"]["payment_method_id"] = $bandeira;
		$val["payment"]["split"] = $parcela;
		$val["payment"]["card_name"] = strtoupper($name);
		$val["payment"]["card_number"] = preg_replace("/[^0-9]/", "", $cartao);
		$val["payment"]["card_expdate_month"] = $mes;
		$val["payment"]["card_expdate_year"] = $ano;
		$val["payment"]["card_cvv"] = $cvv;
		$val["finger_print"] = $finger_print;
		
		if (!isset($json['error'])){
		$resposta = $this->getPay($json_convert);
			
		if ($this->vindi->sandbox()) {
				$this->log->write('DEV PAYLOAD' . json_encode($val));
				$this->log->write('DEV RESPONSE' . json_encode($resposta));
			}
		
		if($resposta['message_response']['message'] == 'success') {
			switch($resposta['data_response']['transaction']['status_id']) {
				case '4':
					$order_status_id = $this->config->get('payment_vindicartao_order_status_id');
					break;
				case '6':
					$order_status_id = $this->config->get('payment_vindicartao_order_status_id2');
					break;
				case '7':
					$order_status_id = $this->config->get('payment_vindicartao_order_status_id1');
					break;
				case '24':
					$order_status_id = $this->config->get('payment_vindicartao_order_status_id3');
					break;
				case '87':
					$order_status_id = $this->config->get('payment_vindicartao_order_status_id5');
					break;
				case '89':
					$order_status_id = $this->config->get('payment_vindicartao_order_status_id4');
					break;
			}
		$comment  = "Situação: " . $resposta['data_response']['transaction']['status_name'] . "\n";
		$comment .= "ID: " . $resposta['data_response']['transaction']['transaction_id'] . "\n";
		$comment .= "Token: " . $resposta['data_response']['transaction']['token_transaction'] . "\n";
		$comment .= "Detalhe: " . $resposta['data_response']['transaction']['payment']['payment_response'] . "\n";
		$comment .= "TID: " . $resposta['data_response']['transaction']['payment']['tid'] . "\n";
		$comment .= "Bandeira: " . $resposta['data_response']['transaction']['payment']['payment_method_name'] . "\n";
		$comment .= "Número de Parcelas: " . $resposta['data_response']['transaction']['payment']['split'] . "\n";
		$json['success'] = "Success";  
		$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status_id, $comment, $notify = true);
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
		$descricao_erro = 'Erro no Processamento do Pagamento';
		}
			
		$json['warning'] = $descricao_erro;
			
		$this->log->write('ERRO API: Vindi Cartao - PEDIDO N: '. $this->session->data['order_id'] . ' - ' .json_encode($resposta));
		}

		}			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getPay($json_convert) {
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
			
			if ($order_info && $this->request->post['token_transaction'] && $this->request->post['transaction']['transaction_id'] && $order_info['payment_code'] == 'vindicartao') {
		        $order_status_ids = $order_info['order_status_id'];
				$order_status_id = $this->config->get('payment_vindicartao_order_status_id');

				switch($this->request->post['transaction']['status_id']) {
					case '4':
						$order_status_id = $this->config->get('payment_vindicartao_order_status_id');
						break;
					case '6':
						$order_status_id = $this->config->get('payment_vindicartao_order_status_id2');
						break;
					case '7':
						$order_status_id = $this->config->get('payment_vindicartao_order_status_id1');
						break;
					case '24':
						$order_status_id = $this->config->get('payment_vindicartao_order_status_id3');
						break;
					case '87':
						$order_status_id = $this->config->get('payment_vindicartao_order_status_id5');
						break;
					case '89':
						$order_status_id = $this->config->get('payment_vindicartao_order_status_id4');
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
	        $this->log->write('ERRO no Retorno: Vindi Cartao - IP '. $this->request->server['REMOTE_ADDR']);
	    }
		
	}
	
	public function validaCPF($doc) {
        if(strlen($doc) > 11) {
            $doc = preg_replace('/[^0-9]/', '', $doc);
            $doc = (string) $doc;
            $doc_original = $doc;
            $primeiros_numeros_cnpj = substr($doc, 0, 12);
            if (!function_exists('multiplica_cnpj')) {
                function multiplica_cnpj($doc, $posicao = 5) {
                    $calculo = 0;
                    for ($i = 0; $i < strlen($doc); $i++) {
                        $calculo = $calculo + ( $doc[$i] * $posicao );
                        $posicao--;
                        if ($posicao < 2) {
                            $posicao = 9;
                        }
                    }
                    return $calculo;
                }

            }

            $primeiro_calculo = multiplica_cnpj($primeiros_numeros_cnpj);
            $primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 : 11 - ( $primeiro_calculo % 11 );
            $primeiros_numeros_cnpj .= $primeiro_digito;
            $segundo_calculo = multiplica_cnpj($primeiros_numeros_cnpj, 6);
            $segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 : 11 - ( $segundo_calculo % 11 );
            $doc = $primeiros_numeros_cnpj . $segundo_digito;
            if ($doc === $doc_original) {
                return true;
            }
				
			} else {
	   
	   if ( ! function_exists('calc_digitos_posicoes') ) {
                function calc_digitos_posicoes( $digitos, $posicoes = 10, $soma_digitos = 0 ) {
                    for ( $i = 0; $i < strlen( $digitos ); $i++  ) {
                        $soma_digitos = $soma_digitos + ( $digitos[$i] * $posicoes );
                        $posicoes--;
                    }
                    $soma_digitos = $soma_digitos % 11;
                    if ( $soma_digitos < 2 ) {
                        $soma_digitos = 0;
                    } else {
                        $soma_digitos = 11 - $soma_digitos;
                    }
                    $doc = $digitos . $soma_digitos;
                    return $doc;
                }
            }
            if ( ! $doc ) {
                return false;
            }
            $doc = preg_replace( '/[^0-9]/is', '', $doc );
            if ( strlen( $doc ) != 11 ) {
                return false;
            }   
            $digitos = substr($doc, 0, 9);
            $novo_cpf = calc_digitos_posicoes( $digitos );
            $novo_cpf = calc_digitos_posicoes( $novo_cpf, 11 );
            if ( $novo_cpf === $doc ) {
                return true;
            } else {
                return false;
            }
			}

    }
	
	public function validaCRT($cartao) {

    $cartao = preg_replace('/\D/', '', $cartao);
    $cartao_length=strlen($cartao);
    $parity = $cartao_length % 2;
    $total=0;
	if (preg_match('/(\d)\1{10}/', $cartao)) {
        return false;
    }
    for ($i = 0; $i < $cartao_length; $i++) {
    $digit = $cartao[$i];
    
    if ($i % 2 == $parity) {
      $digit *= 2;
      if ($digit > 9) {
        $digit -= 9;
      }
    }

    $total += $digit;
  }

  return ($total % 10 == 0) ? true : false;

  }
  
  public function parcela() {
	$this->vindi = new VindiApi($this->registry);
	$json = array();
		
	$this->load->model('checkout/order');
	$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
	$ttotal = $order_info['total'];
	  
	if (!empty($this->config->get('payment_vindicartao_parcela_min')) || $this->config->get('payment_vindicartao_parcela_min') > 0) {
		$parcela = $ttotal / (int)$this->config->get('payment_vindicartao_parcela_min');
		
	if ((int)$parcela == 0) {
		$parcela = 1;
	} else if ((int)$parcela > $this->config->get('payment_vindicartao_parcela')) {
		$parcela = (int)$this->config->get('payment_vindicartao_parcela');
	} else {
		$parcela = (int)$parcela;
	}
		
	} else {
		$parcela = (int)$this->config->get('payment_vindicartao_parcela');
	}

	$parcs = array(); 

	$vals["token_account"]  = $this->config->get('payment_vindicartao_token');
	$vals["price"]  = $ttotal;
	$vals["type_response"]  = "J";

    $resps = $this->vindi->getSplitting($vals);
		
	/*	if ($this->config->get('payment_vindicartao_type') == 0) {
			$url = "https://api.intermediador.sandbox.yapay.com.br/v1/transactions/simulate_splitting";    
		} else {
			$url = "https://api.intermediador.yapay.com.br/v1/transactions/simulate_splitting";
    	}
	
	$header = array('Accept: application/json', 'Content-Type: application/json;charset=UTF-8', 'User-Agent: Aplicação Opencart Master');

	$json_convert = json_encode($vals);
	
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
	$resps = json_decode($response, true);

	*/
   
    if($resps['message_response']['message'] == 'success') {
        
        foreach($resps['data_response']['payment_methods'] as $key => $value) {
                
            if($value['payment_method_id'] == $this->request->get['bandeira_id']) {
             
             foreach($value['splittings'] as $splits) {
				if ($splits['split'] <= $parcela) {
                $parcs[] = array(
                 'text'  => $splits['split'].'X de ' . $this->currency->format($splits['value_split'], $this->session->data['currency']),
			     'value' => $splits['split']
               	);   
                }  
			 }
            }
        }
            $json = array(
				'parc'              => $parcs,
			);
    }
           
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}