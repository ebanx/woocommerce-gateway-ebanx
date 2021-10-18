<?php

use Ebanx\Benjamin\Models\Country;
use EBANX\Plugin\Services\WC_EBANX_Constants;
use EBANX\Plugin\Services\WC_EBANX_Payment_Adapter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Safetypay_Gateway
 */
class WC_EBANX_Safetypay_Gateway extends WC_EBANX_Redirect_Gateway {

	/**
	 *
	 * @var bool
	 */
	private $enabled_in_peru = false;

	/**
	 *
	 * @var bool
	 */
	private $enabled_in_ecuador = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id           = 'ebanx-safetypay';
		$this->method_title = __( 'EBANX - Safetypay', 'woocommerce-gateway-ebanx' );

		$this->title       = 'SafetyPay';
		$this->description = 'Paga con SafetyPay.';

		parent::__construct();

		$this->ebanx_gateway = $this->ebanx->safetyPayOnline();

		$peru_methods    = $this->get_setting_or_default( 'peru_payment_methods', [] );
		$ecuador_methods = $this->get_setting_or_default( 'ecuador_payment_methods', [] );

		$this->enabled_in_peru    = in_array( $this->id, $peru_methods );
		$this->enabled_in_ecuador = in_array( $this->id, $ecuador_methods );

		$this->enabled = $this->enabled_in_peru || $this->enabled_in_ecuador
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
	public function is_available() {
		$country               = $this->get_transaction_address('country');
		$parent_available      = parent::is_available();
		$country_iso           = Country::fromIso($country);
		$available_for_country = $this->ebanx_gateway->isAvailableForCountry( $country_iso );

		if (!empty($country_iso)) {
			if ($country_iso === Country::PERU && !$this->enabled_in_peru) {
				$this->debug_log($this->id . ' is not available because it\'s not enabled in Peru.');
			}

			if ($country_iso === Country::ECUADOR && !$this->enabled_in_ecuador) {
				$this->debug_log($this->id . ' is not available because it\'s not enabled in Ecuador.');
			}

			$this->debug_log($this->id . ($available_for_country ? ' is ' : ' is not ') . 'available to ' . $country_iso);
		}

		return $parent_available
		       && $available_for_country
		       && (
				   ($country_iso === Country::PERU && $this->enabled_in_peru)
				   || ($country_iso === Country::ECUADOR && $this->enabled_in_ecuador)
		       );
	}

	/**
	 * The page of order received, we call them "Thank you pages"
	 *
	 * @param  WC_Order $order The order created.
	 * @return void
	 */
	public static function thankyou_page( $order ) {
		$data = array(
			'data'         => array(),
			'order_status' => $order->get_status(),
			'method'       => 'safetypay',
		);

		parent::thankyou_page( $data );
	}

	/**
	 * The HTML structure on checkout page
	 *
	 * @throws Exception Throws missing param message.
	 */
	public function payment_fields() {
		$message = $this->get_sandbox_form_message( $this->get_transaction_address( 'country' ) );
		wc_get_template(
			'sandbox-checkout-alert.php',
			array(
				'is_sandbox_mode' => $this->is_sandbox_mode,
				'message'         => $message,
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		$description = $this->get_description();
		if ( isset( $description ) ) {
			echo wp_kses_post( wpautop( wptexturize( $description ) ) );
		}

		wc_get_template(
			'safetypay/payment-form.php',
			array(
				'title'       => $this->title,
				'description' => $this->description,
				'id'          => $this->id,
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		$is_peru = WC_EBANX_Constants::COUNTRY_PERU === $this->get_transaction_address( 'country' );

		parent::checkout_rate_conversion( WC_EBANX_Constants::CURRENCY_CODE_PEN, $is_peru );
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return \Ebanx\Benjamin\Models\Payment
	 * @throws Exception Throw parameter missing exception.
	 */
	protected function transform_payment_data( $order ) {
		if ( ! WC_EBANX_Request::has( 'safetypay' ) || ! in_array( WC_EBANX_Request::read( 'safetypay' ), WC_EBANX_Constants::$safetypay_allowed_types ) ) {
			throw new Exception( 'INVALID-SAFETYPAY-TYPE' );
		}

		$data = WC_EBANX_Payment_Adapter::transform( $order, $this->configs, $this->names, $this->id );

		$safetypay_gateway   = 'safetypay' . WC_EBANX_Request::read( 'safetypay' );
		$this->ebanx_gateway = $this->ebanx->{$safetypay_gateway}();

		return $data;
	}
}
