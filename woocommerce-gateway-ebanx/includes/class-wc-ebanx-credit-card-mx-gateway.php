<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Credit_Card_MX_Gateway extends WC_EBANX_Credit_Card_Gateway
{
    const ACQUIRER_MIN_INSTALMENT_VALUE = 100; //MXN

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id = 'ebanx-credit-card-mx';
        $this->method_title = __('EBANX - Credit Card Mexico', 'woocommerce-gateway-ebanx');

        $this->title = __('Tarjeta de Crédito', 'woocommerce-gateway-ebanx');
        $this->description = __('Pay with credit card.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        $this->enabled = is_array($this->configs->settings['mexico_payment_methods']) ? in_array($this->id, $this->configs->settings['mexico_payment_methods']) ? 'yes' : false : false;
    }

    /**
     * Check if the method is available to show to the users
     *
     * @return boolean
     */
    public function is_available()
    {
        return parent::is_available() && $this->getTransactionAddress('country') === WC_Ebanx_Gateway_Utils::COUNTRY_MEXICO;
    }

    /**
     * The HTML structure on checkout page
     */
    public function payment_fields() {
       $cart_total = $this->get_order_total();

        $cards = array_filter((array) get_user_meta($this->userId, '_ebanx_credit_card_token', true), function ($card) {
            return !empty($card->brand) && !empty($card->token) && !empty($card->masked_number);
        });

        \Ebanx\Config::set([
            'integrationKey' => $this->private_key,
            'testMode' => $this->is_sandbox_mode,
        ]);

        $usd_to_mxn = \Ebanx\Ebanx::getExchange(array(
            'currency_code' => 'USD',
            'currency_base_code' => 'MXN'
        ));

        $mxn_value = $cart_total * $usd_to_mxn->currency_rate->rate;
        $acquirer_max_instalments = floor($mxn_value / self::ACQUIRER_MIN_INSTALMENT_VALUE);

        wc_get_template(
            'ebanx-credit-card-mx/payment-form.php',
            array(
                'language' => $this->language,
                'cards' => (array) $cards,
                'cart_total' => $cart_total,
                'country' => $this->getTransactionAddress('country'),
                'max_installment' => min($this->configs->settings['credit_card_instalments'], $acquirer_max_instalments),
                'place_order_enabled' => (isset($this->configs->settings['save_card_data']) && $this->configs->settings['save_card_data'] === 'yes'),
                'instalments' => 'Meses sin intereses',
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }
}
