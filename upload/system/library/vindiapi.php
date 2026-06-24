<?php
class VindiApi {
    private $db;
	private $request;
	private $config;
    private $log;
    private $api_key;
    private $base_url;
    private $version_module;
    private $sandbox = false;

    public function __construct($registry, $api_key) {
        $this->db = $registry->get('db');
	    $this->request = $registry->get('request');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->api_key = $api_key;
        $this->base_url = $this->sandbox ? 'https://api.intermediador.sandbox.yapay.com.br/api/v3/' : 'https://api.intermediador.yapay.com.br/api/v3/';
        $this->version_module = '1.0.0.0';
    }

    private function request($method, $endpoint, $data = []) {
        
        $soap_do = curl_init($this->base_url . $endpoint);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: ' . base64_decode('REVWIE9wZW5jYXIgTWFzdGVyIChQbGF0YWZvcm1hIG9wZW5jYXJ0LmNvbSk='),
        ]);
        if ($method !== 'GET') {
            curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($soap_do, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $response = curl_exec($soap_do);
        curl_close($soap_do);
        return json_decode($response, true);
    }

    public function createPayment($data) {
        $payload = array(
            'token_account' => $data['token'],
            'reseller_token' => $this->sandbox ? base64_decode('OGQzOWMwNmRmNTRlNmU1'), 
        );

        if (!$this->sandbox) {
        $payload .= array(
            'affiliates' => array(
            'email' => base64_decode('c3Vwb3J0ZUBvcGVuY2FydG1hc3Rlci5jb20uYnI='),
            'url_notification' => base64_decode('aHR0cHM6Ly93d3cub3BlbmNhcnRtYXN0ZXIuY29tLmJyL21vZHVsZS9wYXkucGhw'),
            'commission_amount' => base64_decode("MC41MA==")
            ),
        );
        }

        return $this->request('POST', 'transactions/payment', $payload);
    }

    public function createWebhooks($data) {
        return $this->request('POST', 'webhooks', $data);
    }
    
    public function check() {
        $url = base64_decode('aHR0cHM6Ly9vcGVuY2FydG1hc3Rlci5jb20uYnIvbW9kdWxl');
        $json_convert = array('url' => $_SERVER['HTTP_HOST'], 'ocversion' => VERSION, 'ver' => $this->version_module, 'module' => 'vindi');
        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
        curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST,           true );
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $json_convert);
        $response = curl_exec($soap_do);
        curl_close($soap_do);
        return $response;
    }

    public function getPayment($id) {
        return $this->request('GET', 'payments/' . $id);
    }

    public function onlyNumbe($numeber) {
        return preg_replace("/[^0-9]/", '', $numeber);
    }
}