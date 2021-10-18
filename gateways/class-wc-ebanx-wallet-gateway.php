<?php

use Ebanx\Benjamin\Models\Country;
use Ebanx\Benjamin\Models\Payment;
use EBANX\Plugin\Services\WC_EBANX_Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Wallet_Gateway
 */
class WC_EBANX_Wallet_Gateway extends WC_EBANX_Redirect_Gateway {

	/**
	 * Check if the method is available to show to the users
	 * @return boolean
	 * @throws Exception Throws missing parameter message.
	 */
	public function is_available() {
		$country = $this->get_transaction_address( 'country' );
		$parent_available = parent::is_available();
		$country_iso           = Country::fromIso($country);
		$available_for_country = $this->ebanx_gateway->isAvailableForCountry( $country_iso );

		if (!empty($country_iso)) {
			$this->debug_log($this->id . ($available_for_country ? ' is ': ' is not ') . 'available to ' . $country_iso);
		}

		return $parent_available && $available_for_country;
	}

	/**
	 * The HTML structure on checkout page
	 */
	public function payment_fields() {
		$message = $this->get_sandbox_form_message( $this->get_transaction_address( 'country' ) );
		wc_get_template(
			'sandbox-checkout-alert.php',
			[
				'is_sandbox_mode' => $this->is_sandbox_mode,
				'message'         => $message,
			],
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		$description = $this->get_description();
		if ( isset( $description ) ) {
			echo wp_kses_post( wpautop( wptexturize( $description ) ) );
		}

		wc_get_template(
			'wallets/payment-form.php',
			[
				'title'       => $this->title,
				'description' => $this->description,
				'id'          => $this->id,
			],
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		parent::checkout_rate_conversion( WC_EBANX_Constants::CURRENCY_CODE_BRL );

		wc_get_template(
			'bacen-international-alert.php',
			array(
				'is_international' => $this->is_international(),
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);
	}

	/**
	 * Save order's meta fields for future use
	 *
	 * @param WC_Order $order The order created.
	 * @param Object $request The request from EBANX success response.
	 *
	 * @return void
	 * @throws Exception Throw parameter missing exception.
	 */
	protected function save_order_meta_fields( $order, $request ) {
		update_post_meta( $order->get_id(), '_ebanx_wallet', $this->api_name );

		parent::save_order_meta_fields( $order, $request );
	}

	/**
	 * Mount the data to send to EBANX API
	 *
	 * @param WC_Order $order
	 *
	 * @return Payment
	 * @throws Exception Throw parameter missing exception.
	 */
	protected function transform_payment_data( $order ) {
		$data = parent::transform_payment_data( $order );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$data->wallet = $this->api_name;

		return $data;
	}
}
