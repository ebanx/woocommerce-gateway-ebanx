<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Oxxo_Gateway extends WC_EBANX_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-oxxo';
        $this->method_title = __('EBANX - OXXO', 'woocommerce-gateway-ebanx');

        $this->api_name    = 'oxxo';
        $this->title       = __('OXXO', 'woocommerce-gateway-ebanx');
        $this->description = __('Paga con boleta OXXO.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        $this->enabled = is_array($this->configs->settings['mexico_payment_methods']) ? in_array($this->id, $this->configs->settings['mexico_payment_methods']) ? 'yes' : false : false;
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_EBANX_Gateway_Utils::COUNTRY_MEXICO);
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
            array(
                'language' => $this->language,
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        update_post_meta($order->id, '_oxxo_url', $request->payment->oxxo_url);
    }

    public static function thankyou_page($order)
    {
        $oxxo_url = get_post_meta($order->id, '_oxxo_url', true);
        $oxxo_basic = $oxxo_url . "&format=basic";
        $oxxo_pdf = $oxxo_url . "&format=pdf";
        $oxxo_print = $oxxo_url . "&format=print";
        $customer_email = get_post_meta($order->id, '_ebanx_payment_customer_email', true);

        $data = array(
            'url_basic'      => $oxxo_basic,
            'url_pdf'        => $oxxo_pdf,
            'url_print'      => $oxxo_print,
            'url_iframe' => get_site_url() . '/?ebanx=order-received&url=' . $oxxo_basic,
            'customer_email' => $customer_email
        );

        wc_get_template(
            'oxxo/payment-instructions.php',
            $data,
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );

        wp_enqueue_script('woocommerce_ebanx_clipboard', plugins_url('assets/js/vendor/clipboard.min.js', WC_EBANX::DIR));
        wp_enqueue_script('woocommerce_ebanx_order_received', plugins_url('assets/js/order-received.js', WC_EBANX::DIR));
    }

    protected function request_data($order)
    {
        /*TODO: ? if (empty($_POST['ebanx_oxxo_rfc'])) {
        throw new Exception("Missing rfc.");
        }*/

        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = $this->api_name;

        return $data;
    }
}
