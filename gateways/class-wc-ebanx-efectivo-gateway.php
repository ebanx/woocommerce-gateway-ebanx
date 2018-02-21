<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Efectivo_Gateway extends WC_EBANX_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id           = 'ebanx-efectivo';
		$this->method_title = __('EBANX - Efectivo', 'woocommerce-gateway-ebanx');

		$this->api_name    = 'efectivo';
		$this->title       = 'Efectivo';
		$this->description = 'Paga con Efectivo.';

		parent::__construct();

		$this->enabled = is_array($this->configs->settings['argentina_payment_methods']) ? in_array($this->id, $this->configs->settings['argentina_payment_methods']) ? 'yes' : false : false;
	}

	/**
	 * This method always will return false, it doesn't need to show to the customers
	 *
	 * @return boolean Always return false
	 */
	public function is_available()
	{
		return parent::is_available() && $this->getTransactionAddress('country') == WC_EBANX_Constants::COUNTRY_ARGENTINA;
	}

	/**
	 * Check if the currency is processed by EBANX
	 *
	 * @param  string $currency Possible currencies: ARS
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function ebanx_process_merchant_currency($currency) {
		return $currency === WC_EBANX_Constants::CURRENCY_CODE_ARS;
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
			'efectivo/payment-form.php',
			array(
				'id' => $this->id
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		parent::checkout_rate_conversion(WC_EBANX_Constants::CURRENCY_CODE_ARS);
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
		parent::save_order_meta_fields($order, $request);

		update_post_meta($order->id, '_efectivo_url', $request->payment->voucher_url);
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param  WC_Order $order The order created
	 * @return void
	 */
	public static function thankyou_page($order)
	{
		$efectivo_url = get_post_meta($order->id, '_efectivo_url', true);
		$efectivo_basic = $efectivo_url . "&format=basic";
		$efectivo_pdf = $efectivo_url . "&format=pdf";
		$efectivo_print = $efectivo_url . "&format=print";
		$customer_email = get_post_meta($order->id, '_ebanx_payment_customer_email', true);
		$efectivo_hash = get_post_meta($order->id, '_ebanx_payment_hash', true);

		$data = array(
			'data' => array(
				'url_basic'      => $efectivo_basic,
				'url_pdf'        => $efectivo_pdf,
				'url_print'      => $efectivo_print,
				'url_iframe'      => get_site_url() . '/?ebanx=order-received&hash=' . $efectivo_hash . '&payment_type=efectivo',
				'customer_email' => $customer_email
			),
			'order_status' => $order->get_status(),
			'method' => 'efectivo'
		);

		parent::thankyou_page($data);

		wp_enqueue_script('woocommerce_ebanx_clipboard', plugins_url('assets/js/vendor/clipboard.min.js', WC_EBANX::DIR, false, true));
		wp_enqueue_script('woocommerce_ebanx_order_received', plugins_url('assets/js/order-received.js', WC_EBANX::DIR, false, true));
	}

	/**
	 * Mount the data to send to EBANX API
	 *
	 * @param  WC_Order $order
	 * @return array
	 * @throws Exception
	 */
	protected function request_data($order)
	{
		if ( ! WC_EBANX_Request::has('efectivo')
			 || ! in_array(WC_EBANX_Request::read('efectivo'), WC_EBANX_Constants::$VOUCHERS_EFECTIVO_ALLOWED)) {
			throw new Exception('MISSING-VOUCHER');
		}

		$data = parent::request_data($order);

		$data['payment']['payment_type_code'] = WC_EBANX_Request::read('efectivo');

		return $data;
	}
}
