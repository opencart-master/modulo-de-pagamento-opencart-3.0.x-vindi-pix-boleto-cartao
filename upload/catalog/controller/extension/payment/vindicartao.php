<?php
class ControllerExtensionPaymentYapayc extends Controller {
	public function index() {
		$this->load->language('extension/payment/yapayc');
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
		
		if ($this->config->get('payment_yapayc_type') == 0) {
		   $data['sec'] = 'f';
		} else {
		   $data['sec'] = 'v'; 
		}
 
		$data['continue'] = $this->url->link('checkout/success');
		
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$campos = $order_info['custom_field'];
		$ttotal = $order_info['total'];
		$doc = preg_replace("/[^0-9]/", "", $campos[$this->config->get('payment_yapayc_doc')]);
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
		
		return $this->load->view('extension/payment/yapayc', $data);
	}

	public function confirm() {
	    $this->load->language('extension/payment/yapayc');
	    $json = array(); 
		if ($this->session->data['payment_method']['code'] == 'yapayc') {
		include_once('yapayccode.php');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getPay($json_convert) {
	    
	if ($this->config->get('payment_yapayc_type') == 0) {
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
			
			if ($order_info && $this->request->post['token_transaction'] && $this->request->post['transaction']['transaction_id'] && $order_info['payment_code'] == 'yapayc') {
		        $order_status_ids = $order_info['order_status_id'];
				$order_status_id = $this->config->get('payment_yapayc_order_status_id');

				switch($this->request->post['transaction']['status_id']) {
					case '4':
						$order_status_id = $this->config->get('payment_yapayc_order_status_id');
						break;
					case '6':
						$order_status_id = $this->config->get('payment_yapayc_order_status_id2');
						break;
					case '7':
						$order_status_id = $this->config->get('payment_yapayc_order_status_id1');
						break;
					case '24':
						$order_status_id = $this->config->get('payment_yapayc_order_status_id3');
						break;
					case '87':
						$order_status_id = $this->config->get('payment_yapayc_order_status_id5');
						break;
					case '89':
						$order_status_id = $this->config->get('payment_yapayc_order_status_id4');
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
	        $this->log->write('ERRO no Retorno: Yapay Cartao - IP '. $this->request->server['REMOTE_ADDR']);
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
		$json = array();
		
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$ttotal = $order_info['total'];
	  
	    if (!empty($this->config->get('payment_yapayc_parcela_min')) || $this->config->get('payment_yapayc_parcela_min') > 0) {
	    $parcela = $ttotal / (int)$this->config->get('payment_yapayc_parcela_min');
		
		if ((int)$parcela == 0) {
		    $parcela = 1;
		} else if ((int)$parcela > $this->config->get('payment_yapayc_parcela')) {
		    $parcela = (int)$this->config->get('payment_yapayc_parcela');
		} else {
		   	$parcela = (int)$parcela;
		}
		
		} else {
			$parcela = (int)$this->config->get('payment_yapayc_parcela');
		}
		
		if ($this->config->get('payment_yapayc_type') == 0) {
			$url = "https://api.intermediador.sandbox.yapay.com.br/v1/transactions/simulate_splitting";    
		} else {
			$url = "https://api.intermediador.yapay.com.br/v1/transactions/simulate_splitting";
    	}
	
	$header = array('Accept: application/json', 'Content-Type: application/json;charset=UTF-8', 'User-Agent: Aplicação Opencart Master');
	
	$vals["token_account"]  = $this->config->get('payment_yapayc_token');
	$vals["price"]  = $ttotal;
	$vals["type_response"]  = "J";
	$parcs = array(); 
	
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