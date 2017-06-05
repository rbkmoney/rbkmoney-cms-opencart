<?php

class ControllerPaymentRbkmoneyPayment extends Controller
{
    const HEADER_OK = "HTTP/1.0 200 OK";
    const HEADER_BAD_REQUEST = "HTTP/1.0 400 Bad Request";

    /**
     * Constants for Callback
     */

    const ORDER_ID = 'order_id';

    const EVENT_TYPE = 'eventType';

    const INVOICE = 'invoice';
    const INVOICE_ID = 'id';
    const INVOICE_SHOP_ID = 'shopID';
    const INVOICE_METADATA = 'metadata';
    const INVOICE_STATUS = 'status';
    const INVOICE_AMOUNT = 'amount';

    const SIGNATURE = 'HTTP_CONTENT_SIGNATURE';
    const SIGNATURE_ALG = 'alg';
    const SIGNATURE_DIGEST = 'digest';
    const SIGNATURE_PATTERN = "|alg=(\S+);\sdigest=(.*)|i";


    const OPENSSL_VERIFY_SIGNATURE_IS_CORRECT = 1;

    const CHECKOUT_URL = 'https://checkout.rbk.money/checkout.js';

    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        $this->load->language('payment/rbkmoney_payment');
        $this->load->model('checkout/order');
        $this->load->model('payment/rbkmoney_payment');

        $data['payment_form_url'] = static::CHECKOUT_URL;
        $data['payment_form_success_url'] = $this->url->link('checkout/success');
        $data['form_css_button'] = strip_tags($this->config->get('rbkmoney_payment_form_css_button'));
        $data['shop_id'] = $this->config->get('rbkmoney_payment_shop_id');
        $data['form_path_logo'] = $this->config->get('rbkmoney_payment_form_path_logo');
        $data['form_company_name'] = $this->config->get('rbkmoney_payment_form_company_name');
        $data['form_button_label'] = $this->config->get('rbkmoney_payment_form_button_label');
        $data['form_description'] = $this->config->get('rbkmoney_payment_form_description');
        $data['private_key'] = $this->config->get('rbkmoney_payment_private_key');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['order_id'] = $this->session->data['order_id'];

        $invoiceId = '';
        $invoice_access_token = '';

        try {
            $invoiceId = $this->model_payment_rbkmoney_payment->createInvoice($order_info);
            $invoice_access_token = $this->model_payment_rbkmoney_payment->createAccessToken($invoiceId);

            $this->model_checkout_order->addOrderHistory(
                $this->session->data['order_id'],
                $this->config->get('rbkmoney_payment_order_status_progress_id')
            );
        } catch (Exception $ex) {
            $logs = array();
            $logs['error']['message'] = $ex->getMessage();
            $data['errormsg'] = $ex->getMessage();
            $this->model_payment_rbkmoney_payment->logger('exception', $logs);
        }

        $data['invoice_id'] = $invoiceId;
        $data['invoice_access_token'] = $invoice_access_token;

        return $this->load->view('payment/rbkmoney_payment', $data);
    }

    /**
     * http{s}://{your-site}/index.php?route=payment/rbkmoney_payment/callback
     */
    public function callback()
    {
        $content = file_get_contents('php://input');
        $logs = array(
            'request' => array(
                'method' => 'POST',
                'data' => $content,
            ),
        );


        $method = 'notification';
        $this->load->model('payment/rbkmoney_payment');
        $this->model_payment_rbkmoney_payment->logger($method, $logs);

        if (empty($_SERVER[static::SIGNATURE])) {
            $logs['error']['message'] = 'Webhook notification signature missing';
            return $this->outputWithLogger($method, $logs, $logs['error']['message']);
        }

        $logs['signature'] = $_SERVER[static::SIGNATURE];

        $params_signature = $this->model_payment_rbkmoney_payment->getParametersContentSignature($_SERVER[static::SIGNATURE]);
        if (empty($params_signature[static::SIGNATURE_ALG])) {
            $logs['error']['message'] = 'Missing required parameter ' . static::SIGNATURE_ALG;
            return $this->outputWithLogger($method, $logs, $logs['error']['message']);
        }

        if (empty($params_signature[static::SIGNATURE_DIGEST])) {
            $logs['error']['message'] = 'Missing required parameter ' . static::SIGNATURE_DIGEST;
            return $this->outputWithLogger($method, $logs, $logs['error']['message']);
        }

        $signature = $this->model_payment_rbkmoney_payment->urlSafeB64decode($params_signature[static::SIGNATURE_DIGEST]);
        $public_key = $this->config->get('rbkmoney_payment_callback_public_key');
        if (!$this->model_payment_rbkmoney_payment->verificationSignature($content, $signature, $public_key)) {
            $logs['error']['message'] = 'Webhook notification signature mismatch';
            return $this->outputWithLogger($method, $logs, $logs['error']['message']);
        }

        $required_fields = [static::INVOICE, static::EVENT_TYPE];
        $data = json_decode($content, TRUE);

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $logs['error']['message'] = 'One or more required fields are missing';
                return $this->outputWithLogger($method, $logs, $logs['error']['message']);
            }
        }

        $current_shop_id = (int)$this->config->get('rbkmoney_payment_shop_id');
        if ($data[static::INVOICE][static::INVOICE_SHOP_ID] != $current_shop_id) {
            $logs['error']['message'] = static::INVOICE_SHOP_ID . ' is missing';
            return $this->outputWithLogger($method, $logs, $logs['error']['message']);
        }


        if (empty($data[static::INVOICE][static::INVOICE_METADATA][static::ORDER_ID])) {
            $logs['error']['message'] = static::ORDER_ID . ' is missing';
            return $this->outputWithLogger($method, $logs, $logs['error']['message']);
        }


        $order_id = $data[static::INVOICE][static::INVOICE_METADATA][static::ORDER_ID];
        $this->load->model('checkout/order');

        if (!$order_info = $this->model_checkout_order->getOrder($order_id)) {
            $logs['error']['message'] = 'Order ' . $order_id . ' is missing';
            return $this->outputWithLogger($method, $logs, $logs['error']['message']);
        }

        if (!empty($order_info['total'])) {
            $order_amount = (int)$this->model_payment_rbkmoney_payment->prepareAmount($order_info['total']);
            $invoice_amount = (int)$data[static::INVOICE][static::INVOICE_AMOUNT];
            if($order_amount != $invoice_amount) {
                $logs['error']['message'] = 'Received amount vs Order amount mismatch -' . var_dump($data[static::INVOICE][static::INVOICE_AMOUNT]);
                return $this->outputWithLogger($method, $logs, $logs['error']['message']);
            }
        }

        if ($order_info['order_status_id'] == 0) {
            $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('rbkmoney_payment_order_status_id'), 'RBKmoney');
            $logs['order_info'] = $order_info;
            return $this->outputWithLogger($method, $logs, 'OK', self::HEADER_OK);
        }

        if (($data[static::INVOICE][static::INVOICE_STATUS] == 'paid') && ($order_info['order_status_id'] != $this->config->get('rbkmoney_payment_order_status_id'))) {
            $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('rbkmoney_payment_order_status_id'), 'RBKmoney', TRUE);
            $logs['order_info'] = $order_info;
            return $this->outputWithLogger($method, $logs, 'OK', self::HEADER_OK);
        } else {
            $logs['error']['message'] = 'Order ' . $order_id . ' already has a final status';
            return $this->outputWithLogger($method, $logs, $logs['error']['message']);
        }

        return $this->outputWithLogger($method, $logs, 'FINISH', self::HEADER_OK);
    }

    private function outputWithLogger($method, &$logs, $message, $header = self::HEADER_BAD_REQUEST)
    {
        $response = array('message' => $message);
        $this->load->model('payment/rbkmoney_payment');
        $this->model_payment_rbkmoney_payment->logger($method, $logs);
        $this->response->addHeader($header);
        $this->response->setOutput(json_encode($response));
    }

}
