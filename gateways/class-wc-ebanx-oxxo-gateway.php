<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Oxxo_Gateway extends WC_EBANX_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id           = 'ebanx-oxxo';
		$this->method_title = __('EBANX - OXXO', 'woocommerce-gateway-ebanx');

		$this->api_name    = 'oxxo';
		$this->title       = 'OXXO';
		$this->description = 'Paga con boleta OXXO.';

		parent::__construct();

		$this->enabled = is_array($this->configs->settings['mexico_payment_methods']) ? in_array($this->id, $this->configs->settings['mexico_payment_methods']) ? 'yes' : false : false;
	}

	/**
	 * This method always will return false, it doesn't need to show to the customers
	 *
	 * @return boolean Always return false
	 */
	public function is_available()
	{
		return parent::is_available() && $this->getTransactionAddress('country') == WC_EBANX_Constants::COUNTRY_MEXICO;
	}

	/**
	 * Check if the currency is processed by EBANX
	 *
	 * @param  string $currency Possible currencies: MXN
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function ebanx_process_merchant_currency($currency) {
		return $currency === WC_EBANX_Constants::CURRENCY_CODE_MXN;
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
			'oxxo/payment-form.php',
			array(
				'id' => $this->id
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		parent::checkout_rate_conversion(WC_EBANX_Constants::CURRENCY_CODE_MXN);
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

		update_post_meta($order->id, '_oxxo_url', $request->payment->oxxo_url);
		update_post_meta($order->id, '_payment_due_date', $request->payment->due_date);
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param  WC_Order $order The order created
	 * @return void
	 */
	public static function thankyou_page($order)
	{
		$oxxo_url = get_post_meta($order->id, '_oxxo_url', true);
		$oxxo_basic = $oxxo_url . "&format=basic";
		$oxxo_pdf = $oxxo_url . "&format=pdf";
		$oxxo_print = $oxxo_url . "&format=print";
		$customer_email = get_post_meta($order->id, '_ebanx_payment_customer_email', true);
		$oxxo_hash = get_post_meta($order->id, '_ebanx_payment_hash', true);
		$customer_name = $order->billing_first_name;
		$oxxo_due_date = get_post_meta($order->id, '_payment_due_date', true);

		$data = array(
			'data' => array(
				'url_basic'      => $oxxo_basic,
				'url_pdf'        => $oxxo_pdf,
				'url_print'      => $oxxo_print,
				'url_iframe'      => get_site_url() . '/?ebanx=order-received&hash=' . $oxxo_hash . '&payment_type=oxxo',
				'customer_email' => $customer_email,
				'customer_name'   => $customer_name,
				'due_date'        => $oxxo_due_date
			),
			'order_status' => $order->get_status(),
			'method' => 'oxxo'
		);

		parent::thankyou_page($data);

		wp_enqueue_script(
			'woocommerce_ebanx_clipboard',
			plugins_url('assets/js/vendor/clipboard.min.js', WC_EBANX::DIR),
			array(),
			WC_EBANX::get_plugin_version(),
			true
		);
		wp_enqueue_script(
			'woocommerce_ebanx_order_received',
			plugins_url('assets/js/order-received.js', WC_EBANX::DIR),
			array('jquery'),
			WC_EBANX::get_plugin_version(),
			true
		);
	}

	/**
	 * Mount the data to send to EBANX API
	 *
	 * @param  WC_Order $order
	 * @return array
	 */
	protected function request_data($order)
	{
		/*TODO: ? if (empty($_POST['ebanx_oxxo_rfc'])) {
		throw new Exception("Missing rfc.");
		}*/

		$data = parent::request_data($order);
		$data['payment']['payment_type_code'] = $this->api_name;

		return $data;
	}
}
