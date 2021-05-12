<?php

use Ebanx\Benjamin\Models\Country;
use EBANX\Plugin\Services\WC_EBANX_Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Pix_Gateway
 */
class WC_EBANX_Pix_Gateway extends WC_EBANX_New_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id           = 'ebanx-pix';
		$this->method_title = __( 'EBANX - PIX', 'woocommerce-gateway-ebanx' );

		$this->api_name = 'PIX';

		$this->title = 'PIX EBANX';

		$this->description = 'Pague com PIX.';

		parent::__construct();

		$this->ebanx_gateway = $this->ebanx->pix();

		$this->enabled = is_array( $this->configs->settings['brazil_payment_methods'] ) ? in_array( $this->id, $this->configs->settings['brazil_payment_methods'] ) ? 'yes' : false : false;
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 * @throws Exception Throws missing param message.
	 */
	public function is_available() {
		$country = $this->get_transaction_address( 'country' );

		return parent::is_available() && $this->ebanx_gateway->isAvailableForCountry( Country::fromIso( $country ) );
	}

	/**
	 * The HTML structure on checkout page
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
			'pix/checkout-instructions.php',
			array(
				'id' => $this->id,
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		parent::checkout_rate_conversion( WC_EBANX_Constants::CURRENCY_CODE_BRL );
	}

	/**
	 * Mount the data to send to EBANX API
	 *
	 * @param  WC_Order $order
	 * @return array
	 */
	protected function request_data( $order ) {
		$data                                 = parent::request_data( $order );
		$data['payment']['payment_type_code'] = $this->api_name;

		return $data;
	}

	/**
	 * Save order's meta fields for future use
	 *
	 * @param  WC_Order $order The order created.
	 * @param  Object   $request The request from EBANX success response.
	 * @return void
	 */
	protected function save_order_meta_fields( $order, $request ) {
		parent::save_order_meta_fields( $order, $request );

		update_post_meta( $order->get_id(), '_qr_code_value', $request->payment->pix->qr_code_value );
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param  WC_Order $order The order created.
	 * @return void
	 */
	public static function thankyou_page( $order ) {
		$qr_code            = get_post_meta( $order->get_id(), '_qr_code_value', true );
		$customer_email     = get_post_meta( $order->get_id(), '_billing_email', true );
		$customer_name      = get_post_meta( $order->get_id(), '_billing_first_name', true );
		$pix_hash           = get_post_meta( $order->get_id(), '_ebanx_payment_hash', true );

		$data = array(
			'data'         => array(
				'pix_hash'       => $pix_hash,
				'qrcode'         => $qr_code,
				'customer_email' => $customer_email,
				'customer_name'  => $customer_name,
			),
			'order_status' => $order->get_status(),
			'method'       => 'pix',
		);

		parent::thankyou_page( $data );

		wp_enqueue_script(
			'woocommerce_ebanx_clipboard',
			plugins_url( 'assets/js/vendor/clipboard.min.js', WC_EBANX::DIR ),
			array(),
			WC_EBANX::get_plugin_version(),
			true
		);
		wp_enqueue_script(
			'woocommerce_ebanx_order_received',
			plugins_url( 'assets/js/order-received.js', WC_EBANX::DIR ),
			array( 'jquery' ),
			WC_EBANX::get_plugin_version(),
			true
		);
	}
}
