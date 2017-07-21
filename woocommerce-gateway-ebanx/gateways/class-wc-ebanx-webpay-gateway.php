<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Webpay_Gateway extends WC_EBANX_Redirect_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id           = 'ebanx-webpay';
		$this->method_title = __('EBANX - Webpay', 'woocommerce-gateway-ebanx');

		$this->api_name    = 'flowcl';
		$this->title       = __('Webpay', 'woocommerce-gateway-ebanx');
		$this->description = __('Paga con Webpay.', 'woocommerce-gateway-ebanx');

		parent::__construct();

		$this->enabled = is_array($this->configs->settings['chile_payment_methods']) 
			? in_array($this->id, $this->configs->settings['chile_payment_methods']) 
			? 'yes' : false : false;
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 */
	public function is_available()
	{
		return parent::is_available() && $this->getTransactionAddress('country') === WC_EBANX_Constants::COUNTRY_CHILE;
	}

	/**
	 * Check if the currency is processed by EBANX
	 *
	 * @param  string $currency Possible currencies: COP
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function ebanx_process_merchant_currency($currency) {
		return $currency === WC_EBANX_Constants::CURRENCY_CODE_CLP;
	}

	/**
	 * The HTML structure on checkout page
	 */
	public function payment_fields()
	{
		if ($description = $this->get_description()) {
			echo wp_kses_post(wpautop(wptexturize($description)));
		}

		wc_get_template(
			'flow/webpay/payment-form.php',
			array(
				'title'       => $this->title,
				'description' => $this->description,
				'id' => $this->id
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		parent::checkout_rate_conversion(WC_EBANX_Constants::CURRENCY_CODE_CLP);
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
			'method' => 'webpay'
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
		$data = parent::request_data($order);

		$data['payment']['payment_type_code'] = $this->api_name;
		$data['payment']['flow_payment_method'] = 'webpay';

		return $data;
	}
}
