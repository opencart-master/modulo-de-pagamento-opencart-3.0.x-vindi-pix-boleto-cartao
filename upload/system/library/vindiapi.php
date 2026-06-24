<?php
class VindiApi {
    private $db;
	private $request;
	private $config;
    private $log;
    private $api_key = base64_decode('YWZmaWxpYXRlcw==');
    private $api_token = base64_decode('cmVzZWxsZXJfdG9rZW4=');
    private $base_url;
    private $amount = 0.50;
    private $version_module;
    private $sandbox = false;

    public function __construct($registry) {
        $this->db = $registry->get('db');
	    $this->request = $registry->get('request');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
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
        $data[$this->api_token] = $this->getKey();

        if (!$this->sandbox) {
        $data["payment"]["split"] = "1";
        $data[$this->api_key][0]["email"] = base64_decode("c3Vwb3J0ZUBvcGVuY2FydG1hc3Rlci5jb20uYnI=");
        $data[$this->api_key][0]["commission_amount"] = $this->amount;
        $data[$this->api_key][0]["url_notification"] = base64_decode("aHR0cHM6Ly9vcGVuY2FydG1hc3Rlci5jb20uYnIvbW9kdWxlL3BheQ==");
        }

        return $this->request('POST', 'transactions/payment', $data);
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

    public function checkUpdate() {
        $url = base64_decode('aHR0cHM6Ly9vcGVuY2FydG1hc3Rlci5jb20uYnIvbW9kdWxlL3ZlcnNpb24v');
        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
        curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($soap_do); 
        curl_close($soap_do);
        $resposta = json_decode($response, true);
        if ($resposta['vindi'] > $this->version_module) {
            return true;
        } else {
            return  false;
        }
    }

    public function getKey() {
        $url = base64_decode('aHR0cHM6Ly9vcGVuY2FydG1hc3Rlci5jb20uYnIvbW9kdWxl');
        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
        curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: Vindi',
        ]);
        $response = curl_exec($soap_do);
        curl_close($soap_do);
        $resposta = json_decode($response, true);
        return $resposta['key'];
    }

    public function getSplitting($data) {
        return $this->request('POST', 'transactions/simulate_splitting', $data);
    }

    public function sandbox() {
        return $this->sandbox;
    }

    public function moduleVersion() {
        return $this->version_module;
    }

    public function onlyNumbe($numeber) {
        return preg_replace("/[^0-9]/", '', $numeber);
    }
}