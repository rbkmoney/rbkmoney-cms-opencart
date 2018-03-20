<?php

class ModelPaymentRbkmoneyPayment extends Model
{
    /**
     * Create invoice settings
     */
    const CREATE_INVOICE_TEMPLATE_DUE_DATE = 'Y-m-d\TH:i:s\Z';
    const CREATE_INVOICE_DUE_DATE = '+1 days';

    const SIGNATURE = 'HTTP_CONTENT_SIGNATURE';
    const SIGNATURE_ALG = 'alg';
    const SIGNATURE_DIGEST = 'digest';
    const SIGNATURE_PATTERN = "|alg=(\S+);\sdigest=(.*)|i";

    private $api_url = 'https://api.rbk.money/v1/';

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

    private function getHeaders()
    {
        $headers = array();
        $headers[] = 'X-Request-ID: ' . uniqid();
        $headers[] = 'Authorization: Bearer ' . $this->config->get('rbkmoney_payment_private_key');
        $headers[] = 'Content-type: application/json; charset=utf-8';
        $headers[] = 'Accept: application/json';
        return $headers;
    }

    public function createInvoice(array $order_info)
    {
        $data = [
            'shopID' => $this->config->get('rbkmoney_payment_shop_id'),
            'amount' => $this->prepareAmount($order_info['total']),
            'metadata' => $this->prepareMetadata($order_info['order_id']),
            'dueDate' => $this->prepareDueDate(),
            'currency' => $order_info['currency_code'],
            'product' => $order_info['order_id'],
            'cart' => $this->prepareCart(),
            'description' => $this->getProductDescription(),
        ];

        $url = $this->prepareApiUrl('processing/invoices');
        $headers = $this->getHeaders();
        return $this->send($url, 'POST', $headers, json_encode($data, true), 'init_invoice');
    }

    private function prepareCart()
    {
        $lines = [];
        foreach ($this->cart->getProducts() as $product) {
            $item = [];
            $item['product'] = $product['name'];
            $item['quantity'] = (int)$product['quantity'];

            $tax = $this->tax->calculate($product['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax'));

            $price = round($tax, 2, PHP_ROUND_HALF_UP);
            $item['price'] = $this->prepareAmount($price);

            $tax_rates = $this->tax->getRates($product['price'], $product['tax_class_id']);
            if (!empty($tax_rates)) {

                foreach ($tax_rates as $rate) {
                    $rate = $this->getRate($rate['rate']);
                    if ($rate != null) {
                        $taxMode = [
                            'type' => 'InvoiceLineTaxVAT',
                            'rate' => $rate,
                        ];
                        $item['taxMode'] = $taxMode;
                    }
                }


            }
            $lines[] = $item;
        }

        $shippingMethod = $this->session->data['shipping_method'];
        if (!empty($shippingMethod)) {

            if (isset($shippingMethod['cost'])) {
                $item = [];
                $item['product'] = $shippingMethod['title'];
                $item['quantity'] = 1;

                $tax = $this->tax->calculate($shippingMethod['cost'] * $item['quantity'], $shippingMethod['tax_class_id'], $this->config->get('config_tax'));
                $price = round($tax, 2, PHP_ROUND_HALF_UP);
                $item['price'] = $this->prepareAmount($price);

                // Shipping always 18%
                $taxMode = [
                    'type' => 'InvoiceLineTaxVAT',
                    'rate' => "18%",
                ];
                $item['taxMode'] = $taxMode;

                $lines[] = $item;
            }
        }

        return $lines;
    }

    private function getRate($rate)
    {
        switch ($rate) {
            case '0':
            case '0.0000':
                return '0%';
                break;
            case '10':
            case '10.0000':
                return '10%';
                break;
            case '18':
            case '18.0000':
                return '18%';
                break;
            case '10/100':
                return '10/110';
                break;
            case '18/118':
                return '18/118';
                break;
            default:
                return null;
                break;
        }
    }

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
            throw new Exception('Required url parameter not passed');
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
    private function prepareDueDate()
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
    private function prepareMetadata($order_id)
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
    public function prepareAmount($amount)
    {
        return number_format($amount, 2, '.', '') * 100;
    }

    private function prepareApiUrl($path = '', $query_params = [])
    {
        $url = rtrim($this->api_url, '/') . '/' . $path;
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        return $url;
    }

    public function urlSafeB64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function urlSafeB64encode($string)
    {
        $data = base64_encode($string);
        return str_replace(array('+', '/'), array('-', '_'), $data);
    }

    public function getParametersContentSignature($content_signature)
    {
        preg_match_all(static::SIGNATURE_PATTERN, $content_signature, $matches, PREG_PATTERN_ORDER);
        $params = array();
        $params[static::SIGNATURE_ALG] = !empty($matches[1][0]) ? $matches[1][0] : '';
        $params[static::SIGNATURE_DIGEST] = !empty($matches[2][0]) ? $matches[2][0] : '';
        return $params;
    }

    public function verificationSignature($data, $signature, $public_key)
    {
        if (empty($data) || empty($signature) || empty($public_key)) {
            return FALSE;
        }
        $public_key_id = openssl_get_publickey($public_key);
        if (empty($public_key_id)) {
            return FALSE;
        }
        $verify = openssl_verify($data, $signature, $public_key_id, OPENSSL_ALGO_SHA256);
        return ($verify == 1);
    }

    public function logger($method, $message)
    {
        if ($this->config->get('rbkmoney_payment_logs')) {
            $this->log->write('rbkmoney ' . $method . '. ' . print_r($message, true));
        }
    }

}
