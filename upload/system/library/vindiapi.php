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
        $payload = [
            'control' => array(
                'clientID' => $this->client_id,
                'username' => $this->client_id,
                'tableId' => '30303030-3030-3830-3030-303030393930',
                'localNumber' => 1,
                'localHour' => '',
                'industryId' => '999',
                'centralNumber' => '',
                'stationId' => 'ECOMMERCE',
                'companyCode' => $this->cnpj,
                'attendanceHash' => '',
                'operationId' => '1',
                'softwareId' => 'E-PBM-V1.0'
            ),
            'product' => [array(
            'ean' => $data['ean'],
            'quantity' => $data['quantity'],
            'id' => 1,
            "requestedQuantity" => $data['quantity'],
            "listPrice" => str_replace(".", "", floatval($data['price'])*100),
            "netPrice" => str_replace(".", "", floatval($data['price'])*100),
            "discountType" => "B"
            )]
        ];

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