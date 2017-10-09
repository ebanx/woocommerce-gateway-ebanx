<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Credit_Card_CO_Gateway extends WC_EBANX_Credit_Card_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id = 'ebanx-credit-card-co';
		$this->method_title = __('EBANX - Credit Card Colombia', 'woocommerce-gateway-ebanx');
		$this->currency_code = WC_EBANX_Constants::CURRENCY_CODE_COP;

		$this->title = 'Tarjeta de Crédito';
		$this->description = 'Pay with credit card.';

		parent::__construct();

		$this->enabled = is_array($this->configs->settings['mexico_payment_methods']) ? in_array($this->id, $this->configs->settings['colombia_payment_methods']) ? 'yes' : false : false;
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 */
	public function is_available()
	{
		return parent::is_available() && $this->getTransactionAddress('country') === WC_EBANX_Constants::COUNTRY_COLOMBIA;
	}

	/**
	 * Check if the currency is processed by EBANX
	 * @param  string $currency Possible currencies: COP
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function ebanx_process_merchant_currency($currency) {
		return $currency === WC_EBANX_Constants::CURRENCY_CODE_COP;
	}

	/**
	 * The HTML structure on checkout page
	 */
	public function payment_fields() {
		parent::payment_fields();

		parent::checkout_rate_conversion(
			WC_EBANX_Constants::CURRENCY_CODE_COP,
			true,
			null,
			1
		);
	}
}
