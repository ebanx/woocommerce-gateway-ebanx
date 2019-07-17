<?php

use Ebanx\Benjamin\Models\Country;
use EBANX\Plugin\Services\WC_EBANX_Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Pagosnet_Gateway
 */
class WC_EBANX_Pagosnet_Gateway extends WC_EBANX_New_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id           = 'ebanx-pagosnet';
		$this->method_title = __( 'EBANX - PagosNet', 'woocommerce-gateway-ebanx' );

		$this->api_name    = 'pagosnet';
		$this->title       = 'PagosNet';
		$this->description = 'Paga con PagosNet.';

		parent::__construct();

		$this->ebanx_gateway = $this->ebanx->PagosNet();

		$this->enabled = is_array( $this->configs->settings['bolivia_payment_methods'] ) ? in_array( $this->id, $this->configs->settings['bolivia_payment_methods'] ) ? 'yes' : false : false;
	}

	/**
	 * This method always will return false, it doesn't need to show to the customers
	 *
	 * @return boolean Always return false
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
			'pagosnet/payment-form.php',
			array(
				'id' => $this->id,
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		parent::checkout_rate_conversion( WC_EBANX_Constants::CURRENCY_CODE_BOB );
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

		update_post_meta( $order->get_id(), '_voucher_url', $request->payment->voucher_url );
		update_post_meta( $order->get_id(), '_payment_due_date', $request->payment->due_date );
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param  WC_Order $order The order created.
	 * @return void
	 */
	public static function thankyou_page( $order ) {
		$voucher_url     = get_post_meta( $order->get_id(), '_voucher_url', true );
		$voucher_basic   = $voucher_url . '&format=basic';
		$voucher_pdf     = $voucher_url . '&format=pdf';
		$voucher_print   = $voucher_url . '&format=print';
		$customer_email  = get_post_meta( $order->get_id(), '_ebanx_payment_customer_email', true );
		$voucher_hash    = get_post_meta( $order->get_id(), '_ebanx_payment_hash', true );
		$customer_name   = $order->get_billing_first_name();
		$voucher_duedate = get_post_meta( $order->get_id(), '_payment_due_date', true );

		$data = array(
			'data'         => array(
				'url_basic'      => $voucher_basic,
				'url_pdf'        => $voucher_pdf,
				'url_print'      => $voucher_print,
				'url_iframe'     => get_site_url() . '/?ebanx=order-received&hash=' . $voucher_hash,
				'customer_email' => $customer_email,
				'customer_name'  => $customer_name,
				'due_date'       => $voucher_duedate,
			),
			'order_status' => $order->get_status(),
			'method'       => 'pagosnet',
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
}
