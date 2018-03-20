<?php

/**
 * Class ControllerPaymentRBKmoneyPayment
 */
class ControllerPaymentRBKmoneyPayment extends Controller
{

    private $error = array();

    private $settings = array(
        'common_parameters' => array(
            'heading_title',
            'text_edit',
            'text_yes',
            'text_no',

            'button_save',
            'button_cancel',

            // Enable/Disable module
            'entry_status',
            'help_status',
            'text_enabled',
            'text_disabled',

            // Sort order module
            'entry_sort_order',
            'help_sort_order',

            // Enable logs for module
            'entry_logs',

            // Geo Zone
            'text_all_zones',
            'entry_geo_zone',
            'help_geo_zone',

            // Other parameters
            'entry_order_status',
            'help_order_status',

            'entry_shop_id',
            'help_shop_id',

            'entry_form_path_logo',
            'help_form_path_logo',

            'entry_form_css_button',
            'help_form_css_button',

            'entry_form_company_name',
            'help_form_company_name',

            'entry_form_button_label',
            'help_form_button_label',

            'entry_form_description',
            'help_form_description',

            'entry_private_key',
            'help_private_key',

            'entry_callback_public_key',
            'help_callback_public_key',

            'entry_currency',
            'help_currency',

            'entry_order_status_progress',
            'help_order_status_progress',

            'entry_notify_url',
            'help_notify_url',

            'tab_general',
            'tab_custom',
            'tab_additional',
            'tab_docs',

            'docs_integration',
            'docs_webhook',
            'docs_custom',
        ),
        'fields' => array(
            'rbkmoney_payment_status',
            'rbkmoney_payment_sort_order',
            'rbkmoney_payment_geo_zone_id',
            'rbkmoney_payment_logs',
            'rbkmoney_payment_order_status_id',
            'rbkmoney_payment_order_status_progress_id',
            'rbkmoney_payment_form_path_logo',
            'rbkmoney_payment_form_css_button',
            'rbkmoney_payment_form_company_name',
            'rbkmoney_payment_form_button_label',
            'rbkmoney_payment_form_description',
            'rbkmoney_payment_shop_id',
            'rbkmoney_payment_private_key',
            'rbkmoney_payment_callback_public_key',
        ),
        'errors' => array(
            'error_shop_id',
            'error_private_key',
            'error_callback_public_key',
        ),
        'validate' => array(
            array(
                'field' => 'rbkmoney_payment_shop_id',
                'error_name' => 'error_shop_id',
            ),
            array(
                'field' => 'rbkmoney_payment_private_key',
                'error_name' => 'error_private_key',
            ),
            array(
                'field' => 'rbkmoney_payment_callback_public_key',
                'error_name' => 'error_callback_public_key',
            ),
        ),
    );

    public function index()
    {
        $data['rbkmoney_payment_version'] = '1.1 for OpenCart 2.x';

        $this->load->language('payment/rbkmoney_payment');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('rbkmoney_payment', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->prepareUrlLink('extension/payment'));
        }

        $data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning'] : '';

        $data['breadcrumbs'] = [
            array(
                'text' => $this->language->get('text_home'),
                'href' => $this->prepareUrlLink('common/dashboard')
            ),
            array(
                'text' => $this->language->get('text_payment'),
                'href' => $this->prepareUrlLink('extension/payment')
            ),
            array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->prepareUrlLink('payment/rbkmoney_payment')
            )
        ];

        $this->settings['buttons'] = array(
            'action' => $this->prepareUrlLink('payment/rbkmoney_payment'),
            'cancel' => $this->prepareUrlLink('extension/payment'),
        );

        foreach ($this->settings['errors'] as $error) {
            $data[$error] = $this->getErrorByName($error);
        }

        foreach ($this->settings['buttons'] as $name => $value) {
            $data[$name] = $this->language->get($value);
        }

        foreach ($this->settings['common_parameters'] as $common_parameter) {
            $data[$common_parameter] = $this->language->get($common_parameter);
        }

        foreach ($this->settings['fields'] as $field) {
            $data[$field] = trim($this->getConfigByField($field));
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['notify_url'] = $this->getNotifyUrl();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/rbkmoney_payment', $data));
    }

    /**
     * Validate parameters
     *
     * @return bool
     */
    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/rbkmoney_payment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->settings['validate'] as $validate) {
            if (!$this->request->post[$validate['field']]) {
                $this->error[$validate['error_name']] = $this->language->get($validate['error_name']);
            }
        }

        return !$this->error;
    }

    private function getNotifyUrl()
    {
        return HTTPS_CATALOG . 'index.php?route=payment/rbkmoney_payment/callback';
    }

    private function getConfigByField($fieldName)
    {
        return (isset($this->request->post[$fieldName]))
            ? $this->request->post[$fieldName]
            : $this->config->get($fieldName);
    }

    private function prepareUrlLink($link)
    {
        return $this->url->link($link, 'token=' . $this->session->data['token'], 'SSL');
    }


    private function getErrorByName($name, $default_message = '')
    {
        return (isset($this->error[$name])) ? $this->error[$name] : $default_message;
    }
}

?>
