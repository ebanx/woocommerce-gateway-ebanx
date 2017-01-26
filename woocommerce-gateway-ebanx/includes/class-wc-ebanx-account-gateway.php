<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Account_Gateway extends WC_EBANX_Redirect_Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id = 'ebanx-account';
        $this->method_title = __('EBANX - ACCOUNT', 'woocommerce-gateway-ebanx');

        $this->api_name = 'ebanxaccount';
        $this->title = __('Saldo EBANX', 'woocommerce-gateway-ebanx');
        $this->description = __('Pague usando o saldo da sua conta do EBANX.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        // TODO: Put that to father and remove of the all children's
        $this->enabled = is_array($this->configs->settings['brazil_payment_methods']) ? in_array($this->id, $this->configs->settings['brazil_payment_methods']) ? 'yes' : false : false;
    }

    /**
     * Check if the method is available to show to the users
     *
     * @return boolean
     */
    public function is_available()
    {
        return parent::is_available() && $this->getTransactionAddress('country') == WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL;
    }

    /**
     * Mount the data to send to EBANX API
     *
     * @param  WC_Order $order
     * @return array
     */
    protected function request_data($order)
    {
        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = $this->api_name;

        return $data;
    }

    /**
     * The HTML structure on checkout page
     */
    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        wc_get_template(
            'account/payment-form.php',
            array(
                'language' => $this->language,
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }

    /**
     * The page of order received, we call them as "Thank you pages"
     *
     * @param  WC_Order $order The order created
     * @return void
     */
    public static function thankyou_page($order)
    {
        $data = array(
            'data' => array(),
            'order_status' => $order->get_status(),
            'method' => 'account'
        );
        parent::thankyou_page($data);
    }
}
