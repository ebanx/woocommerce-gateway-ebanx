<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Account_Gateway extends WC_EBANX_Redirect_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-account';
        $this->method_title = __('EBANX - ACCOUNT', 'woocommerce-gateway-ebanx');

        $this->api_name    = 'ebanxaccount';
        $this->title       = __('Saldo EBANX', 'woocommerce-gateway-ebanx');
        $this->description = __('Pague usando o saldo da sua conta EBANX.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        // TODO: Put that to father and remove of the all children's
        $this->enabled = in_array($this->id, $this->configs->settings['brazil_payment_methods']) ? 'yes' : false;
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL);
    }

    protected function request_data($order)
    {
        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = $this->api_name;

        return $data;
    }

    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        wc_get_template(
            'account/payment-form.php',
            array(
                'language' => $this->language
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }
}
