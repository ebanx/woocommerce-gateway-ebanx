<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Credit_Card_MX_Gateway extends WC_EBANX_Credit_Card_Gateway
{
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
	 * Check if the currency is processed by EBANX
	 * @param  string $currency Possible currencies: MXN
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function ebanx_process_merchant_currency($currency) {
		return $currency === WC_EBANX_Gateway_Utils::CURRENCY_CODE_MXN;
	}

	/**
	 * The HTML structure on checkout page
	 */
	public function payment_fields() {
		$cart_total = $this->get_order_total();

		$cards = array_filter((array) get_user_meta($this->userId, '_ebanx_credit_card_token', true), function ($card) {
			return !empty($card->brand) && !empty($card->token) && !empty($card->masked_number);
		});

		$max_instalments = $this->fetch_acquirer_max_installments_for_price($cart_total, 'mx');

		wc_get_template(
			'ebanx-credit-card-mx/payment-form.php',
			array(
				'cards' => (array) $cards,
				'cart_total' => $cart_total,
				'country' => $this->getTransactionAddress('country'),
				'max_installment' => min($this->configs->settings['credit_card_instalments'], $max_instalments),
				'installment_taxes' => $this->instalment_rates,
				'place_order_enabled' => (isset($this->configs->settings['save_card_data']) && $this->configs->settings['save_card_data'] === 'yes'),
				'instalments' => 'Meses sin intereses',
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);
	}
}
