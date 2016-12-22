<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Debit_Card_Gateway extends WC_Ebanx_Gateway
{
    public function __construct()
    {
        $this->id           = 'ebanx-debit-card';
        $this->method_title = __('EBANX - Debit Card', 'woocommerce-gateway-ebanx');

        $this->api_name    = 'debitcard';
        $this->title       = __('Debit Card', 'woocommerce-gateway-ebanx');
        $this->description = __('Debit Card description', 'woocommerce-gateway-ebanx');

        parent::__construct();

        $this->enabled = in_array($this->id, $this->configs->settings['mexico_payment_methods']) ? 'yes' : false;
    }

    public function checkout_assets()
    {
        parent::checkout_assets();

        if (is_checkout()) {
            wp_enqueue_script('wc-debit-card-form');
            wp_enqueue_script('woocommerce_ebanx_debit', plugins_url('assets/js/debit-card.js', WC_Ebanx::DIR), array('jquery-payment'), WC_Ebanx::VERSION, true);

            $ebanx_params = array(
                'key'  => $this->public_key,
                'mode' => $this->is_test_mode ? 'test' : 'production',
            );

            wp_localize_script('woocommerce_ebanx', 'wc_ebanx_params', apply_filters('wc_ebanx_params', $ebanx_params));
        }
    }

    public function is_available()
    {
        return parent::is_available() && strtolower($this->getTransactionAddress('country')) === WC_Ebanx_Gateway_Utils::COUNTRY_MEXICO;
    }

    public function payment_fields()
    {
        wc_get_template(
            'debit-card/payment-form.php',
            array(
                'cart_total' => $this->get_order_total()
            ),
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
    }

    public static function thankyou_page($order_id)
    {
        $order = new WC_Order($order_id);

        $data = array(
            'card_brand'  => get_post_meta($order->id, 'Card\'s Brand Name', true)
        );
    }

    protected function request_data($order)
    {
        if (empty($_POST['ebanx_debit_token']) || empty($_POST['ebanx_billing_cvv'])) {
            throw new Exception("Missing ebanx card params.");
        }

        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = $this->api_name;

        // TODO: need fingerprint ?

        $data['payment']['card'] = array(
            'token'    => $_POST['ebanx_debit_token'],
            'card_cvv' => $_POST['ebanx_billing_cvv'],
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
}
