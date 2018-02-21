<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Banking_Ticket_Gateway extends WC_EBANX_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id = 'ebanx-banking-ticket';
		$this->method_title = __('EBANX - Banking Ticket', 'woocommerce-gateway-ebanx');

		$this->api_name = 'boleto';

		$this->title = 'Boleto EBANX';

		$this->description = 'Pague com boleto bancário.';

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
			'banking-ticket/checkout-instructions.php',
			array(
				'id' => $this->id
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		parent::checkout_rate_conversion(WC_EBANX_Constants::CURRENCY_CODE_BRL);
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

		return $data;
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

		update_post_meta($order->id, '_payment_due_date', $request->payment->due_date);
		update_post_meta($order->id, '_boleto_url', $request->payment->boleto_url);
		update_post_meta($order->id, '_boleto_barcode', $request->payment->boleto_barcode);
	}

	/**
	 * Algh to create the barcode of Boleto
	 *
	 * @param  string $code The boleto's code
	 * @return array|string
	 */
	public static function barcode_anti_fraud($code)
	{
		if (strlen($code) != 47) {
			return '';
		}

		return array(
			'boleto1' => '<span>' . substr($code, 0, 5) . '</span>',
			'boleto2' => '<span>' . substr($code, 5, 5) . '</span>',
			'boleto3' => '<span>' . substr($code, 10, 5) . '</span>',
			'boleto4' => '<span>' . substr($code, 15, 6) . '</span>',
			'boleto5' => '<span>' . substr($code, 21, 5) . '</span>',
			'boleto6' => '<span>' . substr($code, 26, 6) . '</span>',
			'boleto7' => '<span>' . substr($code, 32, 1) . '</span>',
			'boleto8' => '<span>' . substr($code, 33, 14) . '</span>',
		);
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param  WC_Order $order The order created
	 * @return void
	 */
	public static function thankyou_page($order)
	{
		$boleto_url = get_post_meta($order->id, '_boleto_url', true);
		$boleto_basic = $boleto_url . "&format=basic";
		$boleto_pdf = $boleto_url . "&format=pdf";
		$boleto_print = $boleto_url . "&format=print";
		$barcode = get_post_meta($order->id, '_boleto_barcode', true);
		$customer_email = get_post_meta($order->id, '_billing_email', true);
		$customer_name = get_post_meta($order->id, '_billing_first_name', true);
		$boleto_due_date = get_post_meta($order->id, '_payment_due_date', true);
		$boleto_hash = get_post_meta($order->id, '_ebanx_payment_hash', true);

		$barcode_anti_fraud = WC_EBANX_Banking_Ticket_Gateway::barcode_anti_fraud($barcode);

		$data = array(
			'data' => array(
				'barcode'         => $barcode,
				'barcode_fraud'   => $barcode_anti_fraud,
				'url_basic'       => $boleto_basic,
				'url_pdf'         => $boleto_pdf,
				'url_print'       => $boleto_print,
				'url_iframe'      => get_site_url() . '/?ebanx=order-received&hash=' . $boleto_hash . '&payment_type=boleto',
				'customer_email'  => $customer_email,
				'customer_name'   => $customer_name,
				'due_date'        => $boleto_due_date,
			),
			'order_status' => $order->get_status(),
			'method' => 'banking-ticket'
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
}
