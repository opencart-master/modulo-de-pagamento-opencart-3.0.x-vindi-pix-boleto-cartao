<?php
class ControllerExtensionFeedVindi extends Controller {
    public function rastreio() {

		$json = array();
		
		if (!isset($this->request->get['order_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			// Add keys for missing post vars
			$keys = array(
				'nrastreio',
				'crastreio',
				'lrastreio'
			);

			foreach ($keys as $key) {
				if (!isset($this->request->post[$key])) {
					$this->request->post[$key] = '';
				}
			}

			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}
			
			if($order_id > 0) {
			    $nrastreio = $this->request->post['nrastreio'];
				$crastreio = $this->request->post['crastreio'];
			    $lrastreio = $this->request->post['lrastreio'];
			    $hoje = date('d/m/Y');
				
				if ($this->config->get('payment_vindiboleto_status')) {
				    $tt = $this->config->get('payment_vindiboleto_token');
				}

                if ($this->config->get('payment_vindicartao_status')) {
				    $tt = $this->config->get('payment_vindicartao_token');
				}
				
				if ($this->config->get('payment_vindipix_status')) {
				    $tt = $this->config->get('payment_vindipix_token');
				} 
			
			    if($this->config->get('payment_vindiboleto_type') == 0 || $this->config->get('payment_vindicartao_type') == 0 || $this->config->get('payment_vindipix_type') == 0) {
				$val3["reseller_token"] = $tt;
                $ckey = "308d71ea40d14d883ff83d7f245aaf8d";
                $csecret = "2850f31fe7619867084a02a86abe803d";
				} else {
                $ckey = "7b35bd7fae66ef80eb69f512210ecaab";
                $csecret = "aac8bac37342026528dad2a2753c0736";
                $val3["reseller_token"] = "8d39c06df54e6e5";
                }
                
                $val3["token_account"] = $tt;
                $val3["consumer_key"] = $ckey;
                $val3["consumer_secret"] = $csecret;
                $val3["type_response"] = "J";
                $jcon3 = json_encode($val3);
                $getcode = $this->getCode($jcon3);
                $con3 = json_decode($getcode, true);
                $val4["consumer_key"] = $ckey;
                $val4["consumer_secret"] = $csecret;
                $val4["code"] = $con3['data_response']['authorization']['code'];
                $val4["type_response"] = "J";
                $jcon4 = json_encode($val4);
                $val5["consumer_key"] = $ckey;
                $val5["consumer_secret"] = $csecret;
                $val5["code"] = $con3['data_response']['authorization']['code'];
                $val5["type_response"] = "J";
                $jcon5 = json_encode($val5);
                if($con3['message_response']['message'] =='success') {
                $gettoken = $this->getToken($jcon4);
                $con4 = json_decode($gettoken, true);
                if($con4['message_response']['message'] =='success') {
                $val['access_token'] = $con4['data_response']['authorization']['access_token'];
				$val['order_number'] = $order_id;
				$val['url'] = $lrastreio;
				$val['code'] = $crastreio;
				$val['date_posting'] = $hoje;
				$val['type_response'] = "J";
				$val['tag_search'] = $nrastreio;
				$jcon = json_encode($val);
				$trace = $this->getTrace($jcon);
				$con = json_decode($trace, true);
				if($con['message_response']['message'] =='success') {
				$json['success'] = "Rastreamento cadastrado com sucesso na API da Vindi!";
				} else {
                $json['error'] = "Ops! Erro ao se conectar com API Vindi";
                $this->log->write($trace);
                }
                } else {
                $expiretoken = $this->expireToken($jcon5);
                $val6["access_token"] = $con4['data_response']['authorization']['access_token'];
                $val6["order_number"] = $con4['data_response']['authorization']['refresh_token'];
                $val6["type_response"] = "J";
                $jcon6 = json_encode($val6);
                $refreshtoken = $this->refreshToken($jcon6);
                $json['error'] = "Ops! Erro ao se conectar com API Vindi";
                $this->log->write($gettoken);
                }
                } else {
                $this->log->write($getcode);
                $json['error'] = "Ops! Erro ao se conectar com API Vindi";    
                }
			    
			} else {
			    $json['error'] = "Ops! Erro ao se conectar com API Vindi";
			}

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
     public function getTrace($jcon) {
    if($this->config->get('payment_vindiboleto_type') == 0 || $this->config->get('payment_vindicartao_type') == 0 || $this->config->get('payment_vindipix_type') == 0) {
    $url = "https://api.intermediador.sandbox.vindi.com.br/api/v1/transactions/trace/";
    } else {
    $url = "https://api.intermediador.vindi.com.br/api/v1/transactions/trace/";
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
   curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $jcon);
   curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
   $response = curl_exec($soap_do); 
   curl_close($soap_do);
  
   return $response;
  }
  
   public function getCode($jcon3) {
    if($this->config->get('payment_vindiboleto_type') == 0 || $this->config->get('payment_vindicartao_type') == 0 || $this->config->get('payment_vindipix_type') == 0) {
    $url = "https://api.intermediador.sandbox.vindi.com.br/api/v1/reseller/authorizations/create/";
    } else {
    $url = "https://api.intermediador.vindi.com.br/api/v1/reseller/authorizations/create/";
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
   curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $jcon3);
   curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
   $response = curl_exec($soap_do); 
   curl_close($soap_do);
  
   return $response;
  }
  
  public function getToken($jcon4) {
    if($this->config->get('payment_vindiboleto_type') == 0 || $this->config->get('payment_vindicartao_type') == 0 || $this->config->get('payment_vindipix_type') == 0) {
    $url = "https://api.intermediador.sandbox.vindi.com.br/api/v1/authorizations/access_token/";
    } else {
    $url = "https://api.intermediador.vindi.com.br/api/v1/authorizations/access_token/";
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
   curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $jcon4);
   curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
   $response = curl_exec($soap_do); 
   curl_close($soap_do);
  
   return $response;
  }
  
  public function expireToken($jcon5) {
    if($this->config->get('payment_vindiboleto_type') == 0 || $this->config->get('payment_vindicartao_type') == 0 || $this->config->get('payment_vindipix_type') == 0) {
    $url = "https://api.intermediador.sandbox.vindi.com.br/api/v1/authorizations/expire/";
    } else {
    $url = "https://api.intermediador.vindi.com.br/api/v1/authorizations/expire/";
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
   curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $jcon5);
   curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
   $response = curl_exec($soap_do); 
   curl_close($soap_do);
  
   return $response;
  }
  
  public function refreshToken($jcon6) {
    if($this->config->get('payment_vindiboleto_type') == 0 || $this->config->get('payment_vindicartao_type') == 0 || $this->config->get('payment_vindipix_type') == 0) {
    $url = "https://api.intermediador.sandbox.vindi.com.br/api/v1/authorizations/refresh/";
    } else {
    $url = "https://api.intermediador.vindi.com.br/api/v1/authorizations/refresh/";
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
   curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $jcon6);
   curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
   $response = curl_exec($soap_do); 
   curl_close($soap_do);
  
   return $response;
  }    
    
}