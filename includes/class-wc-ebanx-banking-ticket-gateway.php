<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Banking_Ticket_Gateway extends WC_Ebanx_Gateway
{

    public function __construct()
    {
        parent::__construct();

        $this->id                   = 'ebanx-banking-ticket';
        $this->icon                 = apply_filters('wc_ebanx_banking_ticket_icon', false);
        $this->has_fields           = true;
        $this->method_title         = __('EBANX - Banking Ticket', 'woocommerce-ebanx');
        $this->method_description   = __('Accept banking ticket payments using EBANX.', 'woocommerce-ebanx');
        $this->view_transaction_url = 'https://dashboard.ebanx.com/#/transactions/%s';

        $this->init_form_fields();

        $this->title          = 'Banking Ticket';
        $this->description    = 'Pay with EBANX Baking Ticket';
        $this->api_key        = $this->get_option('api_key');
        $this->encryption_key = $this->get_option('encryption_key');
        $this->debug          = $this->get_option('debug');

        if ('yes' === $this->debug) {
            $this->log = new WC_Logger();
        }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled'        => array(
                'title'   => __('Enable/Disable', 'woocommerce-ebanx'),
                'type'    => 'checkbox',
                'label'   => __('Enable EBANX Banking Ticket', 'woocommerce-ebanx'),
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
                'description'       => sprintf(__('Please enter your Pagar.me Encryption key. This is needed to process the payment. Is possible get your Encryption Key in %s.', 'woocommerce-ebanx'), '<a href="https://dashboard.ebanx.com/">' . __('Pagar.me Dashboard > My Account page', 'woocommerce-ebanx') . '</a>'),
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

    public function is_available()
    {
        return parent::is_available() && strtolower(WC()->customer->get_shipping_country()) == WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL;
    }

    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        wc_get_template(
            'banking-ticket/checkout-instructions.php',
            array(),
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
    }

    protected function request_data($order)
    {
        $data                                 = parent::request_data($order);
        $data['payment']['payment_type_code'] = 'boleto';
        // TODO: needs due_date ??
        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        update_post_meta($order->id, 'Payment\'s Due Date', $request->payment->due_date);
        update_post_meta($order->id, 'Banking Ticket URL', $request->payment->boleto_url);
        update_post_meta($order->id, 'Banking Ticket Barcode', $request->payment->boleto_barcode);
    }

    public function thankyou_page($order_id)
    {
        $order = wc_get_order($order_id);
        $data  = array(
            'url'      => get_post_meta($order_id, 'Banking Ticket URL', true),
            'barcode'  => get_post_meta($order_id, 'Banking Ticket Barcode', true),
            'due_date' => get_post_meta($order_id, 'Due Date', true),
        );

        wc_get_template(
            'banking-ticket/payment-instructions.php',
            $data,
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
    }
}
