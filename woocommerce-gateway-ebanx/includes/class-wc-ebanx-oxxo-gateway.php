<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Oxxo_Gateway extends WC_Ebanx_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-oxxo';
        $this->method_title = __('EBANX - Oxxo', 'woocommerce-ebanx');

        $this->title       = __('Oxxo');
        $this->description = __('Oxxo description');

        parent::__construct();
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_Ebanx_Gateway_Utils::COUNTRY_MEXICO);
    }

    /**
     * TODO: ??
     * Admin page.
     */
    /*public function admin_options() {
    include dirname( __FILE__ ) . '/admin/views/notices/html-notice-country-not-supported.php';
    }*/

    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        $cart_total = $this->get_order_total();

        wc_get_template(
            'oxxo/payment-form.php',
            array(),
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
    }

    public static function thankyou_page($order_id)
    {
        $order = wc_get_order($order_id);
        $data  = get_post_meta($order_id, '_wc_ebanx_transaction_data', true);

        if (isset($data['installments']) && in_array($order->get_status(), array('processing', 'on-hold'), true)) {
            wc_get_template(
                'oxxo/payment-instructions.php',
                array(),
                'woocommerce/ebanx/',
                WC_Ebanx::get_templates_path()
            );
        }
    }

    protected function request_data($order)
    {
        /*TODO: ? if (empty($_POST['ebanx_oxxo_rfc'])) {
        throw new Exception("Missing rfc.");
        }*/

        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = 'oxxo';

        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        // TODO: What are the fields necessaries by this payment method?
    }
}
