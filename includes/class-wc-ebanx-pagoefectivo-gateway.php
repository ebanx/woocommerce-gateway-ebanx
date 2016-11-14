<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Pagoefectivo_Gateway extends WC_Ebanx_Redirect_Gateway
{

    public function __construct()
    {
        parent::__construct();

        $this->id                   = 'ebanx-pagoefectivo';
        $this->icon                 = apply_filters('wc_ebanx_pagoefectivo_icon', false);
        $this->has_fields           = false;
        $this->method_title         = __('EBANX - Pagoefectivo', 'woocommerce-ebanx');
        $this->method_description   = __('Accept Pagoefectivo payments using EBANX.', 'woocommerce-ebanx');
        $this->view_transaction_url = 'https://dashboard.ebanx.com/#/transactions/%s';

        $this->init_form_fields();

        $this->title          = 'Pagoefectivo';
        $this->description    = 'Pagoefectivo description';
        $this->api_key        = $this->get_option('api_key');
        $this->encryption_key = $this->get_option('encryption_key');
        $this->debug          = $this->get_option('debug');

        if ('yes' === $this->debug) {
            $this->log = new WC_Logger();
        }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_Ebanx_Gateway_Utils::COUNTRY_PERU);
    }

    /**
     * TODO: ??
     * Admin page.
     */
    /*public function admin_options() {
    include dirname( __FILE__ ) . '/admin/views/notices/html-notice-country-not-supported.php';
    }*/

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled'        => array(
                'title'   => __('Enable/Disable', 'woocommerce-ebanx'),
                'type'    => 'checkbox',
                'label'   => __('Enable EBANX Pagoefectivo', 'woocommerce-ebanx'),
                'default' => 'no',
            ),
            'integration'    => array(
                'title'       => __('Integration Settings', 'woocommerce-ebanx'),
                'type'        => 'title',
                'description' => '',
            ),
            'api_key'        => array(
                'title'             => __('EBANX API Key', 'woocommerce-ebanx'),
                'type'              => 'text',
                'description'       => sprintf(__('Please enter your EBANX API Key. This is needed to process the payment and notifications. Is possible get your API Key in %s.', 'woocommerce-ebanx'), '<a href="https://dashboard.ebanx.com/">' . __('EBANX Dashboard > My Account page', 'woocommerce-ebanx') . '</a>'),
                'default'           => '',
                'custom_attributes' => array(
                    'required' => 'required',
                ),
            ),
            'encryption_key' => array(
                'title'             => __('EBANX Encryption Key', 'woocommerce-ebanx'),
                'type'              => 'text',
                'description'       => sprintf(__('Please enter your EBANX Encryption key. This is needed to process the payment. Is possible get your Encryption Key in %s.', 'woocommerce-ebanx'), '<a href="https://dashboard.ebanx.com/">' . __('EBANX Dashboard > My Account page', 'woocommerce-ebanx') . '</a>'),
                'default'           => '',
                'custom_attributes' => array(
                    'required' => 'required',
                ),
            ),
            'testing'        => array(
                'title'       => __('Gateway Testing', 'woocommerce-ebanx'),
                'type'        => 'title',
                'description' => '',
            ),
            'testmode'       => array(
                'type'        => 'checkbox',
                'title'       => __('Test Mode', 'woocommerce-ebanx'),
                'description' => __('Use the test mode on EBANX dashboard to verify everything works before going live.', 'woocommerce-ebanx'),
                'label'       => __('Turn on testing', 'woocommerce-ebanx'),
                'default'     => 'no',
            ),
            'debug'          => array(
                'title'       => __('Debug Log', 'woocommerce-ebanx'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'woocommerce-ebanx'),
                'default'     => 'no',
                'description' => sprintf(__('Log EBANX events, such as API requests. You can check the log in %s', 'woocommerce-ebanx'), '<a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs&log_file=' . esc_attr($this->id) . '-' . sanitize_file_name(wp_hash($this->id)) . '.log')) . '">' . __('System Status &gt; Logs', 'woocommerce-ebanx') . '</a>'),
            ),
        );
    }

    public function thankyou_page($order_id)
    {
        $order = wc_get_order($order_id);
        $data  = get_post_meta($order_id, '_wc_ebanx_transaction_data', true);

        if (isset($data['installments']) && in_array($order->get_status(), array('processing', 'on-hold'), true)) {
            wc_get_template(
                'pagoefectivo/payment-instructions.php',
                array(
                    'title'       => $this->title,
                    'description' => $this->description,
                ),
                'woocommerce/ebanx/',
                WC_Ebanx::get_templates_path()
            );
        }
    }

    protected function process_response($request, $order)
    {
        if ($request->status == 'ERROR' || !$request->payment->cip_url) {
            return $this->process_response_error($request, $order);
        }
        $request->redirect_url = $request->payment->cip_url;

        return parent::process_response($request, $order);
    }

    protected function request_data($order)
    {
        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = 'pagoefectivo';

        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        // TODO: What are the fields necessaries by this payment method?
    }
}
