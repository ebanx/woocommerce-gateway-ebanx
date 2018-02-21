<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Tef_Gateway extends WC_EBANX_Redirect_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id           = 'ebanx-tef';
		$this->method_title = __('EBANX - TEF', 'woocommerce-gateway-ebanx');

		$this->api_name = 'tef';
		$this->title       = 'Débito Online';
		$this->description = 'Selecione o seu banco. A seguir, você será redirecionado para concluir o pagamento pelo seu internet banking.';

		parent::__construct();

		$this->enabled = is_array($this->configs->settings['brazil_payment_methods']) ? in_array($this->id, $this->configs->settings['brazil_payment_methods']) ? 'yes' : false : false;
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 */
	public function is_available()
	{
		return parent::is_available() && $this->getTransactionAddress('country') == WC_EBANX_Constants::COUNTRY_BRAZIL;
	}

	/**
	 * Check if the currency is processed by EBANX
	 *
	 * @param  string $currency Possible currencies: BRL
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function ebanx_process_merchant_currency($currency) {
		return $currency === WC_EBANX_Constants::CURRENCY_CODE_BRL;
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
			'tef/payment-form.php',
			array(
				'title'       => $this->title,
				'description' => $this->description,
				'id' => $this->id
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		parent::checkout_rate_conversion(WC_EBANX_Constants::CURRENCY_CODE_BRL);
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
			'data' => array(
				'bank_name' => get_post_meta($order->id, '_ebanx_tef_bank', true),
				'customer_name' => get_post_meta($order->id, '_billing_first_name', true)
			),
			'order_status' => $order->get_status(),
			'method' => 'tef'
		);

		parent::thankyou_page($data);
	}

	/**
	 * Save order's meta fields for future use
	 *
	 * @param  WC_Order $order The order created
	 * @param  Object $request The request from EBANX success response
	 * @return void
	 */
	protected function save_order_meta_fields($order, $request)
	{
		update_post_meta($order->id, '_ebanx_tef_bank', sanitize_text_field(WC_EBANX_Request::read('tef')));

		parent::save_order_meta_fields($order, $request);
	}

	/**
	 * Mount the data to send to EBANX API
	 *
	 * @param  WC_Order $order
	 * @return array
	 */
	protected function request_data($order)
	{
		if ( ! WC_EBANX_Request::has('tef')
			|| ! in_array(WC_EBANX_Request::read('tef'), WC_EBANX_Constants::$BANKS_TEF_ALLOWED[WC_EBANX_Constants::COUNTRY_BRAZIL])) {
			throw new Exception('MISSING-BANK-NAME');
		}

		$data = parent::request_data($order);

		$data['payment']['payment_type_code'] = WC_EBANX_Request::read('tef');

		return $data;
	}
}
