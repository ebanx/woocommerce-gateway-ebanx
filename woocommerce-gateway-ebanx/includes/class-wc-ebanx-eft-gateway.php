<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Eft_Gateway extends WC_EBANX_Redirect_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-eft';
        $this->method_title = __('EBANX - PSE', 'woocommerce-gateway-ebanx');

        $this->api_name    = 'eft';
        $this->title       = __('PSE - Pago Seguros en Línea', 'woocommerce-gateway-ebanx');
        $this->description = __('Paga con PSE - Pago Seguros en Línea.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        $this->enabled = is_array($this->configs->settings['colombia_payment_methods']) ? in_array($this->id, $this->configs->settings['colombia_payment_methods']) ? 'yes' : false : false;
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_EBANX_Gateway_Utils::COUNTRY_COLOMBIA);
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

        wc_get_template(
            'eft/payment-form.php',
            array(
                'language' => $this->language,
                'title'       => $this->title,
                'description' => $this->description,
                'banks'       => WC_EBANX_Gateway_Utils::$BANKS_EFT_ALLOWED[WC_EBANX_Gateway_Utils::COUNTRY_COLOMBIA]
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }

    public static function thankyou_page($order)
    {
        $data  = get_post_meta($order, '_wc_ebanx_transaction_data', true);

        wc_get_template(
            'eft/payment-instructions.php',
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
        if (!isset($_POST['eft']) || !array_key_exists($_POST['eft'], WC_EBANX_Gateway_Utils::$BANKS_EFT_ALLOWED[WC_EBANX_Gateway_Utils::COUNTRY_COLOMBIA])) {
            throw new Exception('MISSING-BANK-NAME');
        }

        $data = parent::request_data($order);

        $data['payment']['eft_code']          = $_POST['eft'];
        $data['payment']['payment_type_code'] = $this->api_name;

        return $data;
    }
}
