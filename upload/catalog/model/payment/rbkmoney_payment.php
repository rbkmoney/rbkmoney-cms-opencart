<?php

class ModelPaymentRbkmoneyPayment extends Model
{
    /**
     * Create invoice settings
     */
    const CREATE_INVOICE_TEMPLATE_DUE_DATE = 'Y-m-d\TH:i:s\Z';
    const CREATE_INVOICE_DUE_DATE = '+1 days';

    private $api_url = 'https://api.rbk.money/v1/';

    /**
     * Get payment method
     *
     * @param $address
     * @param $total
     * @return array
     */
    public function getMethod($address, $total)
    {
        $this->load->language('payment/rbkmoney');

        if ($this->config->get('rbkmoney_payment_status')) {

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('rbkmoney_payment_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

            if (!$this->config->get('rbkmoney_payment_geo_zone_id')) {
                $status = TRUE;
            } elseif ($query->num_rows) {
                $status = TRUE;
            } else {
                $status = FALSE;
            }
        } else {
            $status = FALSE;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'rbkmoney_payment',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('rbkmoney_payment_sort_order')
            );
        }

        return $method_data;
    }

    /**
     * Create a new invoice.
     *
     * @param array $order_info
     * @return mixed
     */
    public function create_invoice(array $order_info)
    {
        $headers = array();
        $headers[] = 'X-Request-ID: ' . uniqid();
        $headers[] = 'Authorization: Bearer ' . $this->config->get('rbkmoney_payment_private_key');
        $headers[] = 'Content-type: application/json; charset=utf-8';
        $headers[] = 'Accept: application/json';

        $data = [
            'shopID' => (int)$this->config->get('rbkmoney_payment_shop_id'),
            'amount' => $this->prepare_amount($order_info['total']),
            'metadata' => $this->prepare_metadata($order_info['order_id']),
            'dueDate' => $this->prepare_due_date(),
            'currency' => strtoupper($order_info['currency_code']),
            'product' => $order_info['order_id'],
            'description' => $this->getProductDescription(),
        ];

        $url = $this->prepare_api_url('processing/invoices');

        $response = $this->send($url, 'POST', $headers, json_encode($data, true), 'init_invoice');
        $invoice_encode = json_decode($response['body'], true);

        return (!empty($invoice_encode['id'])) ? $invoice_encode['id'] : '';
    }

    /**
     * Get product descriptions from the shopping cart
     *
     * @return string
     */
    private function getProductDescription()
    {
        $products = '';

        $i = 0;
        foreach ($this->cart->getProducts() as $product) {
            if ($i == 0)
                $products .= $product['quantity'] . ' x ' . $product['name'];
            else
                $products .= ', ' . $product['quantity'] . ' x ' . $product['name'];

            $i++;
        }

        if (mb_strlen($products, 'UTF-8') > 255) {
            $products = mb_substr($products, 0, 252, 'UTF-8') . '...';
        }

        return $products;
    }

    /**
     * Create a new token to access the specified invoice.
     *
     * @param $invoice_id
     * @return string
     * @throws Exception
     */
    public function create_access_token($invoice_id)
    {
        if (empty($invoice_id)) {
            throw new Exception('Не передан обязательный параметр invoice_id');
        }
        $headers = array();
        $headers[] = 'X-Request-ID: ' . uniqid();
        $headers[] = 'Authorization: Bearer ' . $this->config->get('rbkmoney_payment_private_key');
        $headers[] = 'Content-type: application/json; charset=utf-8';
        $headers[] = 'Accept: application/json';

        $url = $this->prepare_api_url('processing/invoices/' . $invoice_id . '/access_tokens');

        $response = $this->send($url, 'POST', $headers, '', 'access_tokens');
        if ($response['http_code'] != 201) {
            throw new Exception('Возникла ошибка при создании токена для инвойса');
        }
        $response_decode = json_decode($response['body'], true);
        $access_token = !empty($response_decode['payload']) ? $response_decode['payload'] : '';
        return $access_token;
    }

    /**
     * Send request
     *
     * @param $url
     * @param $method
     * @param array $headers
     * @param string $data
     * @param string $type
     * @return mixed
     * @throws Exception
     */
    private function send($url, $method, $headers = [], $data = '', $type = '')
    {
        $logs = array(
            'request' => array(
                'url' => $url,
                'method' => $method,
                'headers' => $headers,
                'data' => $data,
            ),
        );
        $this->logger($type . ': request', $logs);

        if (empty($url)) {
            throw new Exception('Не передан обязательный параметр url');
        }

        $allowed_methods = ['POST'];
        if (!in_array($method, $allowed_methods)) {
            $this->logger(__CLASS__, $logs);
            throw new Exception('Unsupported method ' . $method);
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        $curl_errno = curl_errno($curl);

        $response['http_code'] = $info['http_code'];
        $response['body'] = $body;
        $response['error'] = $curl_errno;

        $logs['response'] = $response;

        $this->logger($type . ': response', $logs);

        curl_close($curl);

        return $response;
    }

    /**
     * Prepare due date
     *
     * @return string
     */
    private function prepare_due_date()
    {
        date_default_timezone_set('UTC');
        return date(static::CREATE_INVOICE_TEMPLATE_DUE_DATE, strtotime(static::CREATE_INVOICE_DUE_DATE));
    }

    /**
     * Prepare metadata
     *
     * @param $order_id
     * @return array
     */
    private function prepare_metadata($order_id)
    {
        return [
            'cms' => 'opencart',
            'cms_version' => VERSION,
            'module' => 'rbkmoney_payment',
            'order_id' => $order_id,
        ];
    }

    /**
     * Prepare amount (e.g. 124.24 -> 12424)
     *
     * @param $amount int
     * @return int
     */
    private function prepare_amount($amount)
    {
        return number_format($amount, 2, '.', '') * 100;
    }

    /**
     * Prepare API URL
     *
     * @param string $path
     * @param array $query_params
     * @return string
     */
    private function prepare_api_url($path = '', $query_params = [])
    {
        $url = rtrim($this->api_url, '/') . '/' . $path;
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        return $url;
    }

    /**
     * Verification signature
     *
     * @param $data
     * @param $signature
     * @param $public_key
     * @return bool
     */
    public function verification_signature($data, $signature, $public_key)
    {
        if (empty($data) || empty($signature) || empty($public_key)) {
            return FALSE;
        }
        $public_key_id = openssl_get_publickey($public_key);
        if (empty($public_key_id)) {
            return FALSE;
        }
        $verify = openssl_verify($data, $signature, $public_key_id, OPENSSL_ALGO_SHA256);
        return ($verify == static::OPENSSL_VERIFY_SIGNATURE_IS_CORRECT);
    }

    /**
     * Data logging
     *
     * @param $method
     * @param $message
     */
    public function logger($method, $message)
    {
        if ($this->config->get('rbkmoney_payment_logs')) {
            $this->log->write('rbkmoney ' . $method . '. ' . print_r($message, true));
        }
    }

}
