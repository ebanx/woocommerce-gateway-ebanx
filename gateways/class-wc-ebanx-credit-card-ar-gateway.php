<?php

use Ebanx\Benjamin\Models\Country;
use EBANX\Plugin\Services\WC_EBANX_Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Credit_Card_AR_Gateway
 */
class WC_EBANX_Credit_Card_AR_Gateway extends WC_EBANX_Credit_Card_Gateway {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id            = 'ebanx-credit-card-ar';
		$this->method_title  = __( 'EBANX - Credit Card Argentina', 'woocommerce-gateway-ebanx' );
		$this->currency_code = WC_EBANX_Constants::CURRENCY_CODE_ARS;

		$this->title       = 'Tarjeta de Crédito';
		$this->description = 'Pague con tarjeta de crédito.';

		parent::__construct();

		$this->enabled = is_array( $this->configs->settings['argentina_payment_methods'] )
			&& in_array( $this->id, $this->configs->settings['argentina_payment_methods'] )
			? 'yes'
			: false;

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
	public function is_available()
	{
		$country               = $this->get_transaction_address('country');
		$parent_available      = parent::is_available();
		$country_iso           = Country::fromIso($country);
		$available_for_country = $this->ebanx_gateway->isAvailableForCountry( $country_iso );

		if (!empty($country_iso)) {
			if ($country !== Country::ARGENTINA) {
				$this->debug_log($this->id . ' is not available because the transaction address is not Argentina.');
			} else {
				$this->debug_log($this->id . ($available_for_country ? ' is ' : ' is not ') . 'available to ' . $country_iso);
			}
		}

		return $parent_available
			&& $country_iso === Country::ARGENTINA
			&& $available_for_country;
	}

	/**
	 * The HTML structure on checkout page
	 */
	public function payment_fields() {
		parent::payment_fields();

		parent::checkout_rate_conversion(
			WC_EBANX_Constants::CURRENCY_CODE_ARS,
			true,
			null,
			1
		);
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return \Ebanx\Benjamin\Models\Payment
	 * @throws Exception Throws missing parameter exception.
	 */
	protected function transform_payment_data( $order ) {
		$data = parent::transform_payment_data( $order );

		$data->person->documentType = WC_EBANX_Request::read( $this->names['ebanx_billing_argentina_document_type'], null );

		return $data;
	}
}
