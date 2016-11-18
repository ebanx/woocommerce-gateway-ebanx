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

    public function checkout_scripts()
    {
        parent::checkout_scripts();

        if (is_checkout()) {
            wp_enqueue_script('wc-credit-card-form');
            // Using // to avoid conflicts between http and https protocols
            wp_enqueue_script('ebanx_fingerprint', '//downloads.ebanx.com/poc-checkout/src/device-fingerprint.js', '', '1.0', true);
            wp_enqueue_script('ebanx', '//downloads.ebanx.com/poc-checkout/src/ebanx.js', '', '1.0', true);
            wp_enqueue_script('woocommerce_ebanx_jquery_mask', plugins_url('assets/js/jquery-mask.js', WC_Ebanx::DIR), array());
            wp_enqueue_script('woocommerce_ebanx', plugins_url('assets/js/credit-card.js', WC_Ebanx::DIR), array('jquery-payment', 'ebanx'), WC_Ebanx::VERSION, true);

            $ebanx_params = array(
                'key'                  => $this->secret_key,
                'i18n_terms'           => __('Please accept the terms and conditions first', 'woocommerce-gateway-ebanx'),
                'i18n_required_fields' => __('Please fill in required checkout fields first', 'woocommerce-gateway-ebanx'),
                'mode'                 => $this->is_sandbox ? 'test' : 'production',
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

        $cards = array_filter((array) get_user_meta($this->userId, '__ebanx_credit_card_token', true), function($card) {
           return !empty($card->brand) && !empty($card->token) && !empty($card->masked_number); // TODO: Implement token due date
        });

        wc_get_template(
            'credit-card/payment-form.php',
            array(
                'cards'           => (array) $cards,
                'cart_total'      => $cart_total,
                'max_installment' => $this->max_installment,
            ),
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
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
        if (empty($_POST['ebanx_token']) || empty($_POST['ebanx_masked_card_number']) || empty($_POST['ebanx_brand'])) {
            throw new Exception("Missing ebanx card params.");
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

        $data['payment']['payment_type_code'] = $_POST['ebanx_brand'];
        $data['payment']['creditcard']        = array(
            'token' => $_POST['ebanx_token']
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

    protected function save_user_meta_fields($order) {
        parent::save_user_meta_fields($order);

        if($this->userId) {
            $cards = get_user_meta($this->userId, '__ebanx_credit_card_token', true);
            $cards = !empty($cards) ? $cards : [];

            $card = new \stdClass();

            $card->brand = $_POST['ebanx_brand'];
            $card->token = $_POST['ebanx_token'];
            $card->masked_number = $_POST['ebanx_masked_card_number'];

            foreach ($cards as $cd) {
                if ($cd->masked_number == $card->masked_number && $cd->brand == $card->brand) {
                    $cd->token = $card->token;
                    unset($card);
                }
            }

            // TODO: Implement token due date

            $cards[] = $card;

            update_user_meta($this->userId, '__ebanx_credit_card_token', $cards);
        }
    }
}
