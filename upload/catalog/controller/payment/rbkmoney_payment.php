<?php

class ControllerPaymentRbkmoneyPayment extends Controller
{
    const INVOICE_ID = 'invoice_id';
    const PAYMENT_ID = 'payment_id';
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const CREATED_AT = 'created_at';
    const METADATA = 'metadata';
    const STATUS = 'status';
    const SIGNATURE = 'HTTP_X_SIGNATURE';
    const ORDER_ID = 'order_id';

    const CHECKOUT_URL = 'https://checkout.rbk.money/payframe/payframe.js';

    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        $this->load->language('payment/rbkmoney_payment');
        $this->load->model('checkout/order');
        $this->load->model('payment/rbkmoney_payment');

        $data['action'] = static::CHECKOUT_URL;

        $data['shop_id'] = $this->config->get('rbkmoney_payment_shop_id');
        $data['form_path_logo'] = $this->config->get('rbkmoney_payment_form_path_logo');
        $data['form_company_name'] = $this->config->get('rbkmoney_payment_form_company_name');
        $data['private_key'] = $this->config->get('rbkmoney_payment_private_key');
        $data['success_redirect_url'] = $this->url->link('payment/rbkmoney_payment/success_url_redirect');
        $data['failed_redirect_url'] = $this->url->link('payment/rbkmoney_payment/failed_url_redirect');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['amount'] = number_format($order_info['total'], 2, '.', '');
        $data['currency'] = $order_info['currency_code'];

        $data['order_id'] = $this->session->data['order_id'];

        $invoiceId = '';
        $invoice_access_token = '';

        try {
            $response_create_invoice = $this->model_payment_rbkmoney_payment->create_invoice($order_info);
            $create_invoice_encode = json_decode($response_create_invoice['body'], true);
            if (isset($create_invoice_encode['id']) && !empty($invoiceId = $create_invoice_encode['id'])) {
                $invoiceId = $create_invoice_encode['id'];
                $invoice_access_token = $this->model_payment_rbkmoney_payment->create_access_token($invoiceId);
            }

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('rbkmoney_payment_order_status_progress_id'));

            $data['invoice_id'] = $invoiceId;
            $data['invoice_access_token'] = $invoice_access_token;
        } catch (Exception $ex) {
            $logs['error']['message'] = $ex->getMessage();
            $this->model_payment_rbkmoney_payment->logger('exception', $logs);
        }

        $data['invoice_id'] = $invoiceId;
        $data['invoice_access_token'] = $invoice_access_token;

        return $this->load->view('payment/rbkmoney_payment', $data);
    }

    /**
     * http{s}://{your-site}/index.php?route=payment/rbkmoney_payment/success_url_redirect
     */
    public function success_url_redirect()
    {
        header('Location: ' . $this->url->link('checkout/success'), true, 301);
        exit();
    }

    /**
     * http{s}://{your-site}/index.php?route=payment/rbkmoney_payment/failed_url_redirect
     */
    public function failed_url_redirect()
    {
        header('Location: ' . $this->url->link('checkout/payment'), true, 301);
        exit();
    }

    /**
     * http{s}://{your-site}/index.php?route=payment/rbkmoney_payment/callback
     */
    public function callback()
    {
        $this->response->setOutput(header("HTTP/1.0 200 OK"));

        $body = file_get_contents('php://input');
        $logs = array(
            'request' => array(
                'method' => 'POST',
                'data' => $body,
            ),
        );

        $method = 'notification';
        $this->load->model('payment/rbkmoney_payment');
        $this->model_payment_rbkmoney_payment->logger($method, $logs);

        if (empty($_SERVER[static::SIGNATURE])) {
            $logs['error']['message'] = 'Сигнатура отсутствует';
            $this->model_payment_rbkmoney_payment->logger($method, $logs);
            $this->response->setOutput(header("HTTP/1.0 400 Bad Request"));
            exit();
        }

        $required_fields = array(
            static::INVOICE_ID,
            static::PAYMENT_ID,
            static::AMOUNT,
            static::CURRENCY,
            static::CREATED_AT,
            static::METADATA,
            static::STATUS
        );
        $data = json_decode($body, TRUE);

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $logs['error']['message'] = 'Отсутствует обязательное поле';
                $this->model_payment_rbkmoney_payment->logger($method, $logs);
                $this->response->setOutput(header("HTTP/1.0 400 Bad Request"));
                exit();
            }
        }

        if (empty($data[static::METADATA][static::ORDER_ID])) {
            $logs['error']['message'] = 'Отсутствует номер заказа';
            $this->model_payment_rbkmoney_payment->logger($method, $logs);
            $this->response->setOutput(header("HTTP/1.0 400 Bad Request"));
            exit();
        }

        $signature = base64_decode($_SERVER[static::SIGNATURE]);
        $public_key = $this->config->get('rbkmoney_payment_callback_public_key');
        if (!$this->model_payment_rbkmoney_payment->verification_signature($body, $signature, $public_key)) {
            $logs['error']['message'] = 'Сигнатура не совпадает';
            $this->model_payment_rbkmoney_payment->logger($method, $logs);
            $this->response->setOutput(header("HTTP/1.0 400 Bad Request"));
            exit();
        }

        $orderId = $data[static::METADATA][static::ORDER_ID];
        if (!$order_info = $this->model_checkout_order->getOrder($orderId)) {
            $logs['error']['message'] = 'Заказ ' . $orderId . ' не найден';
            $this->model_payment_rbkmoney_payment->logger($method, $logs);
            $this->response->setOutput(header("HTTP/1.0 400 Bad Request"));
            exit();
        }

        if ($order_info['order_status_id'] == 0) {
            $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('rbkmoney_payment_order_status_id'), 'RBKmoney');
            exit;
        }

        if ($data[static::METADATA] == 'paid' && $order_info['order_status_id'] != $this->config->get('rbkmoney_payment_order_status_id')) {
            $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('rbkmoney_payment_order_status_id'), 'RBKmoney', TRUE);
        } else {
            $logs['error']['message'] = 'Заказ ' . $orderId . ' уже имеет финальный статус';
            $this->model_payment_rbkmoney_payment->logger($method, $logs);
            $this->response->setOutput(header("HTTP/1.0 400 Bad Request"));
        }

        exit;
    }

}
