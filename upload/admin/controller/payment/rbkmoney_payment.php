<?php

/**
 * Class ControllerPaymentRBKmoneyPayment
 */
class ControllerPaymentRBKmoneyPayment extends Controller
{
    private $error = array();

    public function index()
    {
        $data['rbkmoney_payment_version'] = '1.1 for OpenCart 2.x';

        $this->load->language('payment/rbkmoney_payment');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('rbkmoney_payment', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], true));
        }

        /**********************************************************************************************
         *                                      Bread crumbs                                          *
         **********************************************************************************************/

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/rbkmoney_payment', 'token=' . $this->session->data['token'], 'SSL')
        );


        /**********************************************************************************************
         *                                         Buttons                                            *
         **********************************************************************************************/

        $data['action'] = $this->url->link('payment/rbkmoney_payment', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');


        /**********************************************************************************************
         *                                    Common parameters                                       *
         **********************************************************************************************/

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Enable/Disable module
        $data['entry_status'] = $this->language->get('entry_status');
        $data['help_status'] = $this->language->get('help_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        if (isset($this->request->post['rbkmoney_payment_status'])) {
            $data['rbkmoney_payment_status'] = $this->request->post['rbkmoney_payment_status'];
        } else {
            $data['rbkmoney_payment_status'] = $this->config->get('rbkmoney_payment_status');
        }

        // Sort order
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['help_sort_order'] = $this->language->get('help_sort_order');

        if (isset($this->request->post['rbkmoney_payment_sort_order'])) {
            $data['rbkmoney_payment_sort_order'] = $this->request->post['rbkmoney_payment_sort_order'];
        } else {
            $data['rbkmoney_payment_sort_order'] = $this->config->get('rbkmoney_payment_sort_order');
        }

        // Geo zone
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['entry_rbkmoney_payment_geo_zone_id'] = $this->language->get('entry_geo_zone');
        $data['help_rbkmoney_payment_geo_zone_id'] = $this->language->get('help_geo_zone');

        if (isset($this->request->post['rbkmoney_payment_geo_zone_id'])) {
            $data['rbkmoney_payment_geo_zone_id'] = $this->request->post['rbkmoney_payment_geo_zone_id'];
        } else {
            $data['rbkmoney_payment_geo_zone_id'] = $this->config->get('rbkmoney_payment_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        // Enable logs
        $data['entry_logs'] = $this->language->get('entry_logs');

        if (isset($this->request->post['rbkmoney_payment_logs'])) {
            $data['rbkmoney_payment_logs'] = $this->request->post['rbkmoney_payment_logs'];
        } else {
            $data['rbkmoney_payment_logs'] = $this->config->get('rbkmoney_payment_logs');
        }

        /**********************************************************************************************
         *                                  Common error warning                                      *
         **********************************************************************************************/

        /** @see validate() */
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        /**********************************************************************************************
         *                                    RBKmoney SETTINGS                                       *
         **********************************************************************************************/

        /**********************************************************************************************
         *                                   PAYMENT ORDER STATUS                                     *
         **********************************************************************************************/

        $data['entry_rbkmoney_payment_order_status_id'] = $this->language->get('entry_order_status');
        $data['help_rbkmoney_payment_order_status_id'] = $this->language->get('help_order_status');

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['rbkmoney_payment_order_status_id'])) {
            $data['rbkmoney_payment_order_status_id'] = $this->request->post['rbkmoney_payment_order_status_id'];
        } else {
            $data['rbkmoney_payment_order_status_id'] = $this->config->get('rbkmoney_payment_order_status_id');
        }


        /**********************************************************************************************
         *                              PAYMENT ORDER STATUS PROGRESS                                 *
         **********************************************************************************************/

        $data['entry_rbkmoney_payment_order_status_progress_id'] = $this->language->get('entry_order_status_progress');
        $data['help_rbkmoney_payment_order_status_progress_id'] = $this->language->get('help_order_status_progress');

        if (isset($this->request->post['rbkmoney_payment_order_status_progress_id'])) {
            $data['rbkmoney_payment_order_status_progress_id'] = $this->request->post['rbkmoney_payment_order_status_progress_id'];
        } else {
            $data['rbkmoney_payment_order_status_progress_id'] = $this->config->get('rbkmoney_payment_order_status_progress_id');
        }


        /**********************************************************************************************
         *                                         SHOP ID                                            *
         **********************************************************************************************/

        $data['entry_shop_id'] = $this->language->get('entry_shop_id');
        $data['help_shop_id'] = $this->language->get('help_shop_id');

        if (isset($this->request->post['rbkmoney_payment_shop_id'])) {
            $data['rbkmoney_payment_shop_id'] = $this->request->post['rbkmoney_payment_shop_id'];
        } else {
            $data['rbkmoney_payment_shop_id'] = $this->config->get('rbkmoney_payment_shop_id');
        }

        /** @see validate() */
        if (isset($this->error['error_shop_id'])) {
            $data['error_shop_id'] = $this->error['error_shop_id'];
        } else {
            $data['error_shop_id'] = '';
        }


        /**********************************************************************************************
         *                                PAYMENT_FORM_PATH_IMG_LOGO                                  *
         **********************************************************************************************/

        $data['entry_form_path_logo'] = $this->language->get('entry_form_path_logo');
        $data['help_form_path_logo'] = $this->language->get('help_form_path_logo');

        if (isset($this->request->post['rbkmoney_payment_form_path_logo'])) {
            $data['rbkmoney_payment_form_path_logo'] = $this->request->post['rbkmoney_payment_form_path_logo'];
        } else {
            $data['rbkmoney_payment_form_path_logo'] = $this->config->get('rbkmoney_payment_form_path_logo');
        }


        /**********************************************************************************************
         *                                 PAYMENT_FORM_COMPANY_NAME                                  *
         **********************************************************************************************/

        $data['entry_form_company_name'] = $this->language->get('entry_form_company_name');
        $data['help_form_company_name'] = $this->language->get('help_form_company_name');

        if (isset($this->request->post['rbkmoney_payment_form_company_name'])) {
            $data['rbkmoney_payment_form_company_name'] = $this->request->post['rbkmoney_payment_form_company_name'];
        } else {
            $data['rbkmoney_payment_form_company_name'] = $this->config->get('rbkmoney_payment_form_company_name');
        }


        /**********************************************************************************************
         *                                   MERCHANT_PRIVATE_KEY                                     *
         **********************************************************************************************/

        $data['entry_private_key'] = $this->language->get('entry_private_key');
        $data['help_private_key'] = $this->language->get('help_private_key');

        if (isset($this->request->post['rbkmoney_payment_private_key'])) {
            $data['rbkmoney_payment_private_key'] = $this->request->post['rbkmoney_payment_private_key'];
        } else {
            $data['rbkmoney_payment_private_key'] = $this->config->get('rbkmoney_payment_private_key');
        }

        /** @see validate() */
        if (isset($this->error['error_private_key'])) {
            $data['error_private_key'] = $this->error['error_private_key'];
        } else {
            $data['error_private_key'] = '';
        }


        /**********************************************************************************************
         *                               MERCHANT_CALLBACK_PUBLIC_KEY                                 *
         **********************************************************************************************/

        $data['entry_callback_public_key'] = $this->language->get('entry_callback_public_key');
        $data['help_callback_public_key'] = $this->language->get('help_callback_public_key');

        if (isset($this->request->post['rbkmoney_payment_callback_public_key'])) {
            $data['rbkmoney_payment_callback_public_key'] = $this->request->post['rbkmoney_payment_callback_public_key'];
        } else {
            $data['rbkmoney_payment_callback_public_key'] = $this->config->get('rbkmoney_payment_callback_public_key');
        }

        /** @see validate() */
        if (isset($this->error['error_callback_public_key'])) {
            $data['error_callback_public_key'] = $this->error['error_callback_public_key'];
        } else {
            $data['error_callback_public_key'] = '';
        }


        /**********************************************************************************************
         *                                         CURRENCY                                           *
         **********************************************************************************************/

        $data['entry_currency'] = $this->language->get('entry_currency');
        $data['help_currency'] = $this->language->get('help_currency');

        if (isset($this->request->post['rbkmoney_payment_currency'])) {
            $data['rbkmoney_payment_currency'] = $this->request->post['rbkmoney_payment_currency'];
        } else {
            $data['rbkmoney_payment_currency'] = $this->config->get('rbkmoney_payment_currency');
        }

        /** @see validate() */
        if (isset($this->error['error_currency'])) {
            $data['error_currency'] = $this->error['error_currency'];
        } else {
            $data['error_currency'] = '';
        }

        /**********************************************************************************************
         *                                      NOTIFICATION URL                                      *
         **********************************************************************************************/

        $data['entry_notify_url'] = $this->language->get('entry_notify_url');
        $data['help_notify_url'] = $this->language->get('help_notify_url');
        $data['notify_url'] = HTTPS_CATALOG . 'index.php?route=payment/rbkmoney_payment/notify';

        /**********************************************************************************************
         *                                         REDIRECT URL                                       *
         **********************************************************************************************/

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/rbkmoney_payment', $data));
    }

    public function install() {
        $this->load->model('payment/rbkmoney_payment');
        $this->model_payment_rbkmoney_payment->install();
    }

    public function uninstall() {
        $this->load->model('payment/rbkmoney_payment');
        $this->model_payment_rbkmoney_payment->uninstall();
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

        if (!$this->request->post['rbkmoney_payment_shop_id']) {
            $this->error['error_shop_id'] = $this->language->get('error_shop_id');
        }

        if (!$this->request->post['rbkmoney_payment_private_key']) {
            $this->error['error_private_key'] = $this->language->get('error_private_key');
        }

        if (!$this->request->post['rbkmoney_payment_callback_public_key']) {
            $this->error['error_callback_public_key'] = $this->language->get('error_callback_public_key');
        }

        if (!$this->request->post['rbkmoney_payment_currency']) {
            $this->error['error_currency'] = $this->language->get('error_currency');
        }

        return !$this->error;
    }

}

?>
