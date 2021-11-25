<?php

use Ebanx\Benjamin\Models\Country;
use EBANX\Plugin\Services\WC_EBANX_Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Credit_Card_CO_Gateway
 */
class WC_EBANX_Credit_Card_CO_Gateway extends WC_EBANX_Credit_Card_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id            = 'ebanx-credit-card-co';
		$this->method_title  = __( 'EBANX - Credit Card Colombia', 'woocommerce-gateway-ebanx' );
		$this->currency_code = WC_EBANX_Constants::CURRENCY_CODE_COP;

		$this->title       = 'Tarjeta de Crédito';
		$this->description = 'Pay with credit card.';

		parent::__construct();

		$this->enabled = is_array( $this->configs->settings['colombia_payment_methods'] ) ? in_array( $this->id, $this->configs->settings['colombia_payment_methods'] ) ? 'yes' : false : false;

		$this->debug_log_if_available('Constructing ' . $this->id . ' gateway');
		$this->debug_log_if_available($this->id . ($this->enabled ? ' is ' : ' is not ') . 'enabled');
		$this->debug_log_if_available($this->id . ' supports ' . implode(', ', $this->supports));
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 * @throws Exception Throws missing param message.
	 */
	public function is_available() {
		$country               = $this->get_transaction_address('country');
		$parent_available      = parent::is_available();
		$available_for_country = $this->ebanx_gateway->isAvailableForCountry(Country::fromIso($country));
		$country_iso           = Country::fromIso($country);

		if (!empty($country_iso)) {
			if ($country !== Country::COLOMBIA) {
				$this->debug_log($this->id . ' is not available because the transaction address is not Colombia.');
			} else {
				$this->debug_log($this->id . ($available_for_country ? ' is ' : ' is not ') . 'available to ' . $country);
			}
		}

		return $parent_available
		       && $country_iso === Country::COLOMBIA
		       && $available_for_country;
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
