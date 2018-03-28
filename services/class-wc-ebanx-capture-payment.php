<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Capture_Payment {
	public static function add_order_capture_button( $actions, $order ) {
		if ($order->get_status() !== 'on-hold'
		    || strpos($order->get_payment_method(), 'ebanx-credit-card') !== 0
		    || !current_user_can('administrator')) {
			return $actions;
		}

		$actions['ebanx_capture'] = array(
			'url'    => static::get_capture_button_url($order),
			'name'   => __( 'Capture payment with EBANX', 'woocommerce-gateway-ebanx' ),
			'action' => "view capture",
		);

		return $actions;
	}

	private static function get_capture_button_url($order) {
		return get_admin_url() . '?ebanx=capture-payment&order_id=' . $order->get_id();
	}

	public static function add_order_capture_button_css() {
		echo '<style>.view.capture::after { font-family: Dashicons; content: "\f316" !important; }</style>';
	}

	public function add_auto_capture_dropdown($actions) {
		global $theorder;

		if (is_array($actions) && $theorder->get_status() === 'on-hold'
		    && strpos($theorder->get_payment_method(), 'ebanx-credit-card') === 0
		    && current_user_can('administrator')) {
			$actions['ebanx_capture_order'] = __('Capture payment on EBANX');
		}

		return $actions;
	}

	public function capture_from_order_dropdown($order) {
		static::capture_payment($order->get_id());
	}

	/**
	 * Captures a credit card payment made while auto capture was disabled
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public static function capture_payment($order_id) {
		$configs = new WC_EBANX_Global_Gateway();
		$order = new WC_Order( $order_id );

		if (!current_user_can('administrator')
		    || $order->get_status() !== 'on-hold'
		    || strpos($order->get_payment_method(), 'ebanx-credit-card') !== 0
		) {
			wp_redirect( get_site_url() );
			return;
		}

		$payment_hash = get_post_meta( $order_id, '_ebanx_payment_hash', true );
		$ebanx = ( new WC_EBANX_Api( $configs ) )->ebanx();
		$response = $ebanx->creditCard()->captureByHash( $payment_hash );
		$error = static::check_capture_errors($response);

		$is_recapture = false;
		if($error){
			$is_recapture = $error->code === 'BP-CAP-4';
			$response['payment']['status'] = $error->status;

			WC_EBANX::log($error->message);
			WC_EBANX_Flash::add_message($error->message, 'warning', true);
		}
		if ( 'CO' === $response['payment']['status'] ) {
			$order->payment_complete();

			if (!$is_recapture) {
				$order->add_order_note(sprintf(__('EBANX: The transaction was captured with the following: %s', 'woocommerce-gateway-ebanx'), wp_get_current_user()->data->user_email));
				WC_EBANX_Flash::add_message(__('Payment ' . $order_id . ' was captured successfully.', 'woocommerce-gateway-ebanx'), 'warning', true);
			}
		}
		else if ( 'CA' === $response['payment']['status'] ) {
			$order->payment_complete();
			$order->update_status('failed');
			$order->add_order_note(__('EBANX: Transaction Failed', 'woocommerce-gateway-ebanx'));
		}
		else if ( 'OP' === $response['payment']['status'] ) {
			$order->update_status('pending');
			$order->add_order_note(__('EBANX: Transaction Pending', 'woocommerce-gateway-ebanx'));
		}
	}

	/**
	 * Checks for errors during capture action
	 * Returns an object with error code, message and target status
	 *
	 * @param array $response The response from EBANX API.
	 * @return stdClass
	 */
	public static function check_capture_errors($response) {
		if ( 'SUCCESS' === $response['status'] ) {
			return null;
		}

		$code = $response['code'];

		// translators: placeholders turn into bp-dr codes.
		$message = sprintf( __( 'EBANX - Unknown error, enter in contact with Ebanx and inform this error code: %s.', 'woocommerce-gateway-ebanx' ), $response['payment']['status_code'] );
		$status = $response['payment']['status'];

		switch ( $response['status_code'] ) {
			case 'BC-CAP-3':
				$message = __('EBANX - Payment cannot be captured, changing it to Failed.', 'woocommerce-gateway-ebanx');
				$status = 'CA';
				break;
			case 'BP-CAP-4':
				$message = __('EBANX - Payment has already been captured, changing it to Processing.', 'woocommerce-gateway-ebanx');
				$status = 'CO';
				break;
			case 'BC-CAP-5':
				$message = __('EBANX - Payment cannot be captured, changing it to Pending.', 'woocommerce-gateway-ebanx');
				$status = 'OP';
				break;
		}

		return (object)array(
			'code' => $code,
			'message' => $message,
			'status' => $status
		);
	}

	private static function get_integration_key($config) {
		return static::is_sandbox($config) ? $config->settings['sandbox_private_key'] : $config->settings['live_private_key'];
	}

	private static function is_sandbox($config) {
		return $config->settings['sandbox_mode_enabled'] === 'yes';
	}
}
