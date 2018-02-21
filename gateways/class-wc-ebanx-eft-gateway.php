<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Eft_Gateway extends WC_EBANX_Redirect_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id           = 'ebanx-eft';
		$this->method_title = __('EBANX - PSE', 'woocommerce-gateway-ebanx');

		$this->api_name    = 'eft';
		$this->title       = 'PSE - Pago Seguros en Línea';
		$this->description = 'Paga con PSE - Pago Seguros en Línea.';

		parent::__construct();

		$this->enabled = is_array($this->configs->settings['colombia_payment_methods']) ? in_array($this->id, $this->configs->settings['colombia_payment_methods']) ? 'yes' : false : false;
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 */
	public function is_available()
	{
		return parent::is_available() && $this->getTransactionAddress('country') == WC_EBANX_Constants::COUNTRY_COLOMBIA;
	}

	/**
	 * Check if the currency is processed by EBANX
	 *
	 * @param  string $currency Possible currencies: COP
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function ebanx_process_merchant_currency($currency) {
		return $currency === WC_EBANX_Constants::CURRENCY_CODE_COP;
	}

	/**
	 * The HTML structure on checkout page
	 */
	public function payment_fields()
	{
		$message = $this->get_sandbox_form_message( $this->getTransactionAddress( 'country' ) );
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
			'eft/payment-form.php',
			array(
				'title'       => $this->title,
				'description' => $this->description,
				'banks'       => WC_EBANX_Constants::$BANKS_EFT_ALLOWED[WC_EBANX_Constants::COUNTRY_COLOMBIA],
				'id' => $this->id
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		parent::checkout_rate_conversion(WC_EBANX_Constants::CURRENCY_CODE_COP);
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
			'method' => 'debit-card'
		);

		parent::thankyou_page($data);
	}

	/**
	 * Mount the data to send to EBANX API
	 *
	 * @param  WC_Order $order
	 * @return array
	 */
	protected function request_data($order)
	{
		if ( ! WC_EBANX_Request::has('eft')
			|| ! array_key_exists(WC_EBANX_Request::read('eft'), WC_EBANX_Constants::$BANKS_EFT_ALLOWED[WC_EBANX_Constants::COUNTRY_COLOMBIA])) {
			throw new Exception('MISSING-BANK-NAME');
		}

		$data = parent::request_data($order);

		$data['payment']['eft_code']          = WC_EBANX_Request::read('eft');
		$data['payment']['payment_type_code'] = $this->api_name;

		return $data;
	}
}
