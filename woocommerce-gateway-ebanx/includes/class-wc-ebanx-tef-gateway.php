<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Tef_Gateway extends WC_EBANX_Redirect_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-tef';
        $this->method_title = __('EBANX - TEF', 'woocommerce-gateway-ebanx');

        $this->title       = __('Débito Online', 'woocommerce-gateway-ebanx');
        $this->description = __('Selecione o seu banco. A seguir, você será redirecionado para concluir o pagamento pelo seu internet banking.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        $this->enabled = is_array($this->configs->settings['brazil_payment_methods']) ? in_array($this->id, $this->configs->settings['brazil_payment_methods']) ? 'yes' : false : false;
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL);
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
            'tef/payment-form.php',
            array(
                'language' => $this->language,
                'title'       => $this->title,
                'description' => $this->description,
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }

    public static function thankyou_page($order)
    {
        $data  = get_post_meta($order, '_wc_ebanx_transaction_data', true);

        wc_get_template(
            'tef/payment-instructions.php',
            array(
                'title'       => $this->title,
                'description' => $this->description,
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }

    protected function request_data($order)
    {
        if (!isset($_POST['tef']) || !in_array($_POST['tef'], WC_EBANX_Gateway_Utils::$BANKS_TEF_ALLOWED[WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL])) {
            throw new Exception('MISSING-BANK-NAME');
        }

        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = $_POST['tef'];

        return $data;
    }
}
