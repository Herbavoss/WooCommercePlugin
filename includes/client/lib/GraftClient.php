<?php

namespace GraftClient;

class GraftClient
{
    const VERSION = '1.0.0';
    const USER_AGENT = 'GRAFT-PHP';

    private $api_url = 'https://localhost:44393';
    private $api_test_url = 'https://localhost:44393';
    private $env = 'test';
    private $api_version = 'v1.0';
    private $api_key = null;
    private $api_secret = null;
    private $user_agent = '';
    private $timeout = 80;
    private $connectiontimeout = 30;

    function __construct($init = array())
    {
        $this->api_version = isset($init['api_version']) ? $init['api_version'] : $this->api_version;
        $this->user_agent = isset($init['user_agent']) ? $init['user_agent'] : self::USER_AGENT . '/' . self::VERSION . '/' . phpversion();
        if (isset($init['api_key']))
            $this->api_key = $init['api_key'];
        if (isset($init['api_secret']))
            $this->api_secret = $init['api_secret'];
        if (isset($init['env']))
            $this->env = $init['env'];
    }

    public function call($resource = null, $method = 'get', $params = array())
    {
        error_log("[method call]: " .$resource);

        if (empty($resource) || empty($this->api_url) || empty($this->api_key) || empty($this->api_secret))
            throw new \Exception('The request was unacceptable, often due to missing a required parameter');

        $environments = array('live', 'test');
        if (!in_array($this->env, $environments)) {
            throw new \Exception('GRAFT API env does not exist');
        }

        $method = strtolower($method);
        $timestamp = (int)(microtime(true));
        $sign = hash_hmac('sha256', $timestamp . $this->api_key, $this->api_secret);

        $opts = array();
        $headers = array();

        $headers = array();
        $headers[] = 'Graft-Access-Key: ' . $this->api_key;
        $headers[] = 'Graft-Access-Sign: ' . $sign;
        $headers[] = 'Graft-Access-Timestamp: ' . $timestamp;

        $curl = curl_init();

        if ($method == 'post') {
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        $opts[CURLOPT_URL] = ($this->env === 'test' ? $this->api_test_url : $this->api_url) . '/' . $this->api_version . '/' . $resource;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = $this->connectiontimeout;
        $opts[CURLOPT_TIMEOUT] = $this->timeout;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_USERAGENT] = $this->user_agent;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;

        curl_setopt_array($curl, $opts);
        $rbody = curl_exec($curl);
        $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($rbody, true);

        if ($rcode === 200) {
        return $result;
        } else {
        throw new \Exception(isset($result['message']) ? $result['message'] : '');
        }
    }

    public function testApi()
    {
        try {
            return $this->call('test/', 'get');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getPayment($payment_id)
    {
        try {
            return $this->call('OnlineGetSaleStatus?id=' . $payment_id, 'get');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function addPayment($params = array())
    {
        try {
            return $this->call('OnlineSale', 'post', $params);
        } catch (\Exception $e) {
            error_log("Add payment exception" .$e->getMessage());
            return false;
        }
    }

}