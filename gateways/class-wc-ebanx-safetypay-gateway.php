<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Safetypay_Gateway extends WC_EBANX_Redirect_Gateway
{
	private $enabled_in_peru = false;
	private $enabled_in_ecuador = false;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id           = 'ebanx-safetypay';
		$this->method_title = __('EBANX - Safetypay', 'woocommerce-gateway-ebanx');

		$this->title       = 'SafetyPay';
		$this->description = 'Paga con SafetyPay.';

		parent::__construct();

		$peru_methods = $this->get_setting_or_default('peru_payment_methods', []);
		$ecuador_methods = $this->get_setting_or_default('ecuador_payment_methods', []);

		$this->enabled_in_peru = in_array($this->id, $peru_methods);
		$this->enabled_in_ecuador = in_array($this->id, $ecuador_methods);

		$this->enabled = $this->enabled_in_peru || $this->enabled_in_ecuador
			? 'yes'
			: false;
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 * @throws Exception Throws missing param message.
	 */
	public function is_available()
	{
		$country = $this->get_transaction_address( 'country' );
		$is_peru = $country == WC_EBANX_Constants::COUNTRY_PERU;
		$is_ecuador = $country == WC_EBANX_Constants::COUNTRY_ECUADOR;

		return parent::is_available()
			&& (
				($is_peru && $this->enabled_in_peru)
				|| ($is_ecuador && $this->enabled_in_ecuador)
			);
	}

	/**
	 * Check if the currency is processed by EBANX
	 *
	 * @param  string $currency Possible currencies: PEN for PERU and globals for ECUADOR
	 *
	 * @return boolean          Return true if EBANX process the currency.
	 * @throws Exception        Throws missing param message.
	 */
	public function ebanx_process_merchant_currency($currency) {
		$country = $this->get_transaction_address( 'country' );

		switch ($country) {
			case WC_EBANX_Constants::COUNTRY_PERU:
				return WC_EBANX_Constants::CURRENCY_CODE_PEN;
			default:
				return null;
		}
	}

	/**
	 * The page of order received, we call them "Thank you pages"
	 *
	 * @param  WC_Order $order The order created
	 * @return void
	 */
	public static function thankyou_page($order)
	{
		$data = array(
			'data' => array(),
			'order_status' => $order->get_status(),
			'method' => 'safetypay'
		);

		parent::thankyou_page($data);
	}

	/**
	 * The HTML structure on checkout page
	 * @throws Exception Throws missing param message.
	 */
	public function payment_fields()
	{
		$message = $this->get_sandbox_form_message( $this->get_transaction_address( 'country' ) );
		wc_get_template(
			'sandbox-checkout-alert.php',
			array(
				'is_sandbox_mode' => $this->is_sandbox_mode,
				'message' => $message,
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		if ($description = $this->get_description()) {
			echo wp_kses_post(wpautop(wptexturize($description)));
		}

		wc_get_template(
			'safetypay/payment-form.php',
			array(
				'title'       => $this->title,
				'description' => $this->description,
				'id' => $this->id
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		$is_peru = WC_EBANX_Constants::COUNTRY_PERU === $this->get_transaction_address( 'country' );

		parent::checkout_rate_conversion(WC_EBANX_Constants::CURRENCY_CODE_PEN, $is_peru);
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return \Ebanx\Benjamin\Models\Payment
	 * @throws Exception Throw parameter missing exception.
	 */
	protected function transform_payment_data( $order ) {
		if ( ! WC_EBANX_Request::has( 'safetypay' ) || ! in_array( WC_EBANX_Request::read( 'safetypay' ), WC_EBANX_Constants::$safetypay_allowed_types ) ) {
			throw new Exception('INVALID-SAFETYPAY-TYPE');
		}

		$data = WC_EBANX_Payment_Adapter::transform( $order, $this->configs, $this->names );

		$safetypay_gateway = 'safetypay' . WC_EBANX_Request::read( 'safetypay' );
		$this->ebanx_gateway = $this->ebanx->{$safetypay_gateway}();

		return $data;
	}
}
