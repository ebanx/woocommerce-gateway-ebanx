<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Sencillito_Gateway extends WC_EBANX_Redirect_Gateway
{
    public function __construct()
    {
        $this->id           = 'ebanx-sencillito';
        $this->method_title = __('EBANX - Sencillito', 'woocommerce-gateway-ebanx');

        $this->api_name    = 'sencillito';
        $this->title       = __('Sencillito');
        $this->description = __('Pay with Sencillito.');

        parent::__construct();

        $this->enabled = in_array($this->id, $this->configs->settings['chile_payment_methods']) ? 'yes' : false;
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_EBANX_Gateway_Utils::COUNTRY_CHILE);
    }

    protected function request_data($order)
    {
        $data                                 = parent::request_data($order);
        $data['payment']['payment_type_code'] = $this->api_name;

        return $data;
    }
}
