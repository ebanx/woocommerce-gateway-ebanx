<?php

class WC_EBANX_Hooks {
	/**
	 * Initiliazer
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', __CLASS__ . '::payment_status_hook_action' );
	}

	/**
	 * Check if the url has the response type
	 *
	 * @return boolean
	 */
	private static function is_url_response() {
		$urlResponse = ( WC_EBANX_Request::has('hash')
			&& WC_EBANX_Request::has('merchant_payment_code')
			&& WC_EBANX_Request::has('payment_type_code') );

		if ( $urlResponse ) {
			WC_EBANX_Request::set('notification_type', 'UPDATE');
		}

		return $urlResponse;
	}

	/**
	 * Process future hooks for cash payments like TEF, OXXO, etc
	 *
	 * @return boolean
	 */
	public static function payment_status_hook_action() {
		ob_start();

		// $myfile = fopen("/var/www/checkout-woocommerce/test.txt", "a") or die("Unable to open file!");
		// fwrite($myfile, json_encode(array('get' => $_GET, 'post' => $_REQUEST, 'request' => $_REQUEST)));

		if ( ( WC_EBANX_Request::has('operation')
			&& WC_EBANX_Request::read('operation') == 'payment_status_change'
			&& WC_EBANX_Request::has('notification_type')
			&& ( WC_EBANX_Request::has('hash_codes')
			|| WC_EBANX_Request::has('codes') ) )
			|| self::is_url_response()
		) {
			$codes = array();

			if ( WC_EBANX_Request::has('hash_codes') ) {
				$codes['hash'] = WC_EBANX_Request::read('hash_codes');
			}

			if ( WC_EBANX_Request::has('hash') ) {
				$codes['hash'] = WC_EBANX_Request::read('hash');
			}

			if ( WC_EBANX_Request::has('codes') ) {
				$codes['merchant_payment_code'] = WC_EBANX_Request::read('codes');
			}

			if ( WC_EBANX_Request::has('merchant_payment_code') ) {
				$codes['merchant_payment_code'] = WC_EBANX_Request::read('merchant_payment_code');
			}

			$ebanx = new WC_EBANX_Gateway();
			$order = $ebanx->process_hook( $codes, WC_EBANX_Request::read('notification_type') );

			if ( self::is_url_response() ) {
				wp_redirect( $order->get_checkout_order_received_url() );
				exit;
			}
		}

		ob_end_clean();

		return true;
	}
}

WC_EBANX_Hooks::init();
