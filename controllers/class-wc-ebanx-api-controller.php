<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

class WC_EBANX_Api_Controller {
	private $config;

	/**
	 * Construct the controller with our config object
	 *
	 * @param WC_EBANX_Global_Gateway $config
	 */
	public function __construct(WC_EBANX_Global_Gateway $config) {
		$this->config = $config;
	}

	/**
	 * Responds that the plugin is installed
	 *
	 * @return void
	 */
	public function dashboard_check() {
		echo json_encode(array(
			'ebanx' => true,
			'version' => WC_EBANX::get_plugin_version()
		));
	}

	/**
	 * Responds that the plugin logs
	 *
	 * @return void
	 * @throws Exception Throws missing param message.
	 */
	public function retrieve_logs() {
		header( 'Content-Type: application/json' );

		if ( empty( WC_EBANX_Request::has( 'integration_key' ) )
			|| ( WC_EBANX_Request::read( 'integration_key' ) !== $this->config->settings['live_private_key']
			&& WC_EBANX_Request::read( 'integration_key' ) !== $this->config->settings['sandbox_private_key'] ) ) {
			die( json_encode( [] ) );
		}

		$logs = WC_EBANX_Database::select( 'logs' );

		WC_EBANX_Database::truncate( 'logs' );

		die( json_encode( $logs ) );
	}

	/**
	 * Captures a credit card payment made while auto capture was disabled
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function capture_payment($order_id) {
		WC_EBANX_Capture_Payment::capture_payment( $order_id );

		wp_redirect($this->get_admin_order_url($order_id));
	}

	/**
	 * @return String
	 */
	public function get_admin_order_url($order_id) {
		return admin_url() . 'post.php?post=' . $order_id . '&action=edit';
	}

	/**
	 * Cancels an open cash payment order with "On hold" status
	 *
	 * @param $order_id
	 * @param $user_id
	 *
	 * @return void
	 */
	public function cancel_order( $order_id, $user_id ) {
		$order = new WC_Order( $order_id );
		if ( $user_id != get_current_user_id()
		    || $order->get_status() !== 'on-hold'
		    || ! in_array( $order->get_payment_method(), WC_EBANX_Constants::$CASH_PAYMENTS_GATEWAYS_CODE )
			) {
			wp_redirect( get_site_url() );
			return;
		}

		$hash = get_post_meta($order_id, '_ebanx_payment_hash', true);

		$ebanx = ( new WC_EBANX_Api( $this->config ) )->ebanx();

		try {
			$response = $ebanx->cancelPayment()->request($hash);

			WC_EBANX_Cancel_Logger::persist([
				'paymentHash' => $hash,
				'$response' => $response,
			]);

			if ($response['status'] === 'SUCCESS') {
				$order->update_status('cancelled', __('EBANX: Cancelled by customer', 'woocommerce-gateway-ebanx'));
			}

			wp_redirect($order->get_view_order_url());

		} catch (Exception $e) {
			$message = $e->getMessage();
			WC_EBANX::log("EBANX Error: $message");

			wc_add_notice($message, 'error');
			wp_redirect( get_site_url() );
		}
	}

	/**
	 * Gets the banking ticket HTML by cUrl with url fopen fallback
	 *
	 * @return void
	 */
	public function order_received($hash, $payment_type) {
		$url = $this->get_url($hash, $payment_type);

		if (!in_array('curl', get_loaded_extensions())) {
			echo file_get_contents($url);
			return;
		}

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$html = curl_exec($curl);

		if (curl_error($curl)) {
			return;
		}

		curl_close($curl);
		echo $html;
	}

	private function get_url($hash, $payment_type) {
		$is_sandbox = $this->config->get_setting_or_default('sandbox_mode_enabled', 'yes') === 'yes';

		$subdomain = $is_sandbox ? 'sandbox' : 'print';
		$url = "https://{$subdomain}.ebanx.com/";

		if ($payment_type !== 'cip') {
			$url .= 'print/';
		}

		if ($payment_type === 'efectivo') {
			$url .= 'voucher/';
		}

		if ($payment_type !== null && $payment_type !== 'boleto' && $payment_type !== 'efectivo') {
			$url .= "{$payment_type}/";
		}

		$url .= "?hash={$hash}";

		if ($payment_type !== 'baloto') {
			$url .= '&format=basic#';
		}

		return $url;
	}
}
