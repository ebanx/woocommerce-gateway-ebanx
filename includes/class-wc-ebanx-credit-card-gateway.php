<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Credit_Card_Gateway extends WC_Ebanx_Gateway
{

    public function __construct()
    {
        parent::__construct();

        $this->id                   = 'ebanx-credit-card';
        $this->icon                 = apply_filters('wc_ebanx_credit_card_icon', false);
        $this->has_fields           = true;
        $this->method_title         = __('EBANX - Credit Card', 'woocommerce-ebanx');
        $this->method_description   = __('Accept credit card payments using EBANX.', 'woocommerce-ebanx');
        $this->view_transaction_url = 'https://dashboard.ebanx.com/#/transactions/%s';

        // Load the form fields.
        $this->init_form_fields();

        // Define user set variables.
        $this->title           = 'Credit Card';
        $this->description     = 'Credit Card description';
        $this->api_key         = $this->get_option('api_key');
        $this->encryption_key  = $this->get_option('encryption_key');
        $this->checkout        = $this->get_option('checkout');
        $this->max_installment = $this->get_option('max_installment', '12');
        $this->debug           = $this->get_option('debug');

        if ('yes' === $this->debug) {
            $this->log = new WC_Logger();
        }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, __CLASS__ . '::thankyou_page');
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled'         => array(
                'title'   => __('Enable/Disable', 'woocommerce-ebanx'),
                'type'    => 'checkbox',
                'label'   => __('Enable EBANX Credit Card', 'woocommerce-ebanx'),
                'default' => 'no',
            ),
            'integration'     => array(
                'title'       => __('Integration Settings', 'woocommerce-ebanx'),
                'type'        => 'title',
                'description' => '',
            ),
            'api_key'         => array(
                'title'             => __('EBANX API Key', 'woocommerce-ebanx'),
                'type'              => 'text',
                'description'       => sprintf(__('Please enter your EBANX API Key. This is needed to process the payment and notifications. Is possible get your API Key in %s.', 'woocommerce-ebanx'), '<a href="https://dashboard.ebanx.com/">' . __('EBANX Dashboard > My Account page', 'woocommerce-ebanx') . '</a>'),
                'default'           => '',
                'custom_attributes' => array(
                    'required' => 'required',
                ),
            ),
            'encryption_key'  => array(
                'title'             => __('EBANX Encryption Key', 'woocommerce-ebanx'),
                'type'              => 'text',
                'description'       => sprintf(__('Please enter your EBANX Encryption key. This is needed to process the payment. Is possible get your Encryption Key in %s.', 'woocommerce-ebanx'), '<a href="https://dashboard.ebanx.com/">' . __('EBANX Dashboard > My Account page', 'woocommerce-ebanx') . '</a>'),
                'default'           => '',
                'custom_attributes' => array(
                    'required' => 'required',
                ),
            ),
            'max_installment' => array(
                'title'       => __('Number of Installment', 'woocommerce-ebanx'),
                'type'        => 'select',
                'class'       => 'wc-enhanced-select',
                'default'     => '12',
                'description' => __('Maximum number of installments possible with payments by credit card.', 'woocommerce-ebanx'),
                'desc_tip'    => true,
                'options'     => array(
                    '1'  => '1',
                    '2'  => '2',
                    '3'  => '3',
                    '4'  => '4',
                    '5'  => '5',
                    '6'  => '6',
                    '7'  => '7',
                    '8'  => '8',
                    '9'  => '9',
                    '10' => '10',
                    '11' => '11',
                    '12' => '12',
                ),
            ),
            'testing'         => array(
                'title'       => __('Gateway Testing', 'woocommerce-ebanx'),
                'type'        => 'title',
                'description' => '',
            ),
            'testmode'        => array(
                'type'        => 'checkbox',
                'title'       => __('Test Mode', 'woocommerce-ebanx'),
                'description' => __('Use the test mode on EBANX dashboard to verify everything works before going live.', 'woocommerce-ebanx'),
                'label'       => __('Turn on testing', 'woocommerce-ebanx'),
                'default'     => 'no',
            ),
            'debug'           => array(
                'title'       => __('Debug Log', 'woocommerce-ebanx'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'woocommerce-ebanx'),
                'default'     => 'no',
                'description' => sprintf(__('Log EBANX events, such as API requests. You can check the log in %s', 'woocommerce-ebanx'), '<a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs&log_file=' . esc_attr($this->id) . '-' . sanitize_file_name(wp_hash($this->id)) . '.log')) . '">' . __('System Status &gt; Logs', 'woocommerce-ebanx') . '</a>'),
            ),
        );
    }

    public function checkout_scripts()
    {
        parent::checkout_scripts();

        if (is_checkout()) {
            wp_enqueue_script('wc-credit-card-form');
            // Using // to avoid conflicts between http and https protocols
            wp_enqueue_script('ebanx_fingerprint', '//downloads.ebanx.com/poc-checkout/src/device-fingerprint.js', '', '1.0', true);
            wp_enqueue_script('ebanx', '//downloads.ebanx.com/poc-checkout/src/ebanx.js', '', '1.0', true);
            wp_enqueue_script('woocommerce_ebanx', plugins_url('assets/js/credit-card.js', WC_Ebanx::DIR), array('jquery-payment', 'ebanx'), WC_Ebanx::VERSION, true);

            $ebanx_params = array(
                'key'                  => $this->settings['encryption_key'],
                'i18n_terms'           => __('Please accept the terms and conditions first', 'woocommerce-gateway-ebanx'),
                'i18n_required_fields' => __('Please fill in required checkout fields first', 'woocommerce-gateway-ebanx'),
                'mode'                 => ($this->settings['testmode'] === 'yes') ? 'test' : 'production',
            );

            // If we're on the pay page we need to pass ebanx.js the address of the order.
            if (is_checkout_pay_page() && isset($_GET['order']) && isset($_GET['order_id'])) {
                $order_key = urldecode($_GET['order']);
                $order_id  = absint($_GET['order_id']);
                $order     = wc_get_order($order_id);

                if ($order->id === $order_id && $order->order_key === $order_key) {
                    $ebanx_params['billing_first_name'] = $order->billing_first_name;
                    $ebanx_params['billing_last_name']  = $order->billing_last_name;
                    $ebanx_params['billing_address_1']  = $order->billing_address_1;
                    $ebanx_params['billing_address_2']  = $order->billing_address_2;
                    $ebanx_params['billing_state']      = $order->billing_state;
                    $ebanx_params['billing_city']       = $order->billing_city;
                    $ebanx_params['billing_postcode']   = $order->billing_postcode;
                    $ebanx_params['billing_country']    = $order->billing_country;
                }
            }

            wp_localize_script('woocommerce_ebanx', 'wc_ebanx_params', apply_filters('wc_ebanx_params', $ebanx_params));
        }
    }

    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        $cart_total = $this->get_order_total();

        if ('no' === $this->checkout) {
            wc_get_template(
                'credit-card/payment-form.php',
                array(
                    'cart_total'      => $cart_total,
                    'max_installment' => $this->max_installment,
                ),
                'woocommerce/ebanx/',
                WC_Ebanx::get_templates_path()
            );
        } else {
            echo '<div id="ebanx-checkout-params" ';
            echo 'data-total="' . esc_attr($cart_total * 100) . '" ';
            echo 'data-max_installment="' . esc_attr(apply_filters('wc_ebanx_checkout_credit_card_max_installments', 1/* TODO: $this->api->get_max_installment( $cart_total )*/)) . '"';
            echo '></div>';
        }
    }

    public static function thankyou_page($order)
    {
        $data = array(
            'instalments' => get_post_meta($order->id, 'Number of Instalments', true),
            'card_brand'  => get_post_meta($order->id, 'Card\'s Brand Name', true),
        );

        if (isset($data['instalments'])) {
            wc_get_template(
                'credit-card/payment-instructions.php',
                $data,
                'woocommerce/ebanx/',
                WC_Ebanx::get_templates_path()
            );
        }
    }

    protected function request_data($order)
    {
        if (empty($_POST['ebanx_token'])) {
            throw new Exception("Missing token.");
        }

        if (empty($_POST['ebanx_device_fingerprint'])) {
            throw new Exception("Missing Device fingerprint.");
        }

        $data = parent::request_data($order);

        if (trim(strtolower(WC()->customer->get_shipping_country())) === WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL ||
            trim(strtolower(WC()->customer->get_shipping_country())) === WC_Ebanx_Gateway_Utils::COUNTRY_MEXICO
        ) {
            if (empty($_POST['ebanx_billing_installments'])) {
                throw new Exception('Please, provide a number of instalments.');
            }

            $data['payment']['instalments'] = $_POST['ebanx_billing_installments'];
        }

        $data['device_id'] = $_POST['ebanx_device_fingerprint'];

        $data['payment']['payment_type_code'] = 'visa'; // TODO: Dynamic
        $data['payment']['creditcard']        = array(
            'token' => $_POST['ebanx_token'], // TODO: get from ?
        );

        return $data;
    }

    protected function process_response($request, $order)
    {
        if ($request->status == 'ERROR' || !$request->payment->pre_approved) {
            return $this->process_response_error($request, $order);
        }

        parent::process_response($request, $order);
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        update_post_meta($order->id, 'Card\'s Brand Name', $request->payment->payment_type_code);
        update_post_meta($order->id, 'Number of Instalments', $request->payment->instalments);
    }
}
