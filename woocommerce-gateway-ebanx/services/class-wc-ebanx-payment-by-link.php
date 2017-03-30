<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class WC_EBANX_Payment_By_Link {

	private static $errors = array();
	private static $post_id;
	private static $order;
	private static $config;

	public static function create($post_id, $config){
		self::$post_id = $post_id;
		self::$order = wc_get_order($post_id);
		self::$config = $config;

		if ( ! self::can_create_payment() ) {
			return;
		}

		if ( self::validate() ) {
			self::send_errors();
			return;
		}

		$request = self::send_request();
		if ( $request && $request->status !== 'SUCCESS' ) {
			// self::$errors[] = 'We couldn\'t create your EBANX order. Could you review your fields and try again?';
			// self::send_errors();
			var_dump($request);
			exit;
			return;
		}

		self::post_request($request->payment->hash, $request->redirect_url);
	}

	/**
	 * Checks if user has permissions to save data.
	 * AND Checks if not an autosave.
	 * AND Checks if not a revision.
	 *
	 * @return bool Can we create a payment by link now?
	 */
	private function can_create_payment() {
		return current_user_can( 'edit_post', self::$post_id )
				&& ! wp_is_post_autosave( self::$post_id )
				&& ! wp_is_post_revision( self::$post_id );
	}

	private function validate() {
		if ( ! self::$order->status === 'pending' ) {
			self::$errors[] = 'You can only create payment links on pending orders.';
			return count(self::$errors);
		}
		if ( self::$order->get_total() < 1 ) {
			self::$errors[] = 'The total amount needs to be greater than $1.';
		}
		if ( ! in_array(strtolower(self::$order->billing_country), WC_EBANX_Constants::$ALL_COUNTRIES) ) {
			self::$errors[] = 'EBANX only support the countries: Brazil, Mexico, Peru, Colombia and Chile. Please, use one of these.';
		}
		if ( empty(self::$order->billing_email) ) {
			self::$errors[] = 'The customer e-mal is required, please provide a valid customer e-mail.';
		}
		if ( ! empty(self::$order->payment_method) ) {
			if ( ! array_key_exists(self::$order->payment_method, WC_EBANX_Constants::$GATEWAY_TO_PAYMENT_TYPE_CODE) ) {
				self::$errors[] = 'EBANX does not support the selected payment method.';
			}
			if ( array_key_exists(strtolower(self::$order->billing_country), WC_EBANX_Constants::$EBANX_GATEWAYS_BY_COUNTRY)
				&& ! in_array(self::$order->payment_method, WC_EBANX_Constants::$EBANX_GATEWAYS_BY_COUNTRY[strtolower(self::$order->billing_country)]) ) {
				self::$errors[] = 'The selected payment method is not available on the selected country.';
			}
		}
		return count(self::$errors);
	}

	private function send_errors() {
		WC_EBANX_Flash::clear_messages();
		foreach (self::$errors as $error) {
			WC_EBANX_Flash::add_message(__($error, 'woocommerce-gateway-ebanx'));
		}
	}

	private function send_request() {
		$data = array(
			'name'                  => self::$order->billing_first_name . ' ' . self::$order->billing_last_name,
			'email'                 => self::$order->billing_email,
			'country'               => strtolower(self::$order->billing_country),
			'payment_type_code'     => empty(self::$order->payment_method) ? '_all' : WC_EBANX_Constants::$GATEWAY_TO_PAYMENT_TYPE_CODE[self::$order->payment_method],
			'merchant_payment_code' => self::$order->id . '_' . md5(time()),
			'currency_code'         => strtoupper(get_woocommerce_currency()),
			'amount'                => self::$order->get_total()
		);
		var_dump($data);

		\Ebanx\Config::set(self::$config);
		\Ebanx\Config::setDirectMode(false);

		$request = false;

		try {
			$request = \Ebanx\EBANX::doRequest($data);
		} catch (Exception $e) {
			self::$errors[] = $e->getMessage();
			self::send_errors();
		}

		return $request;
	}

	private function post_request($hash, $url) {
		self::$order->update_status('on-hold', __('Order created via EBANX.', 'woocommerce-gateway-ebanx'));
		update_post_meta(self::$post_id, '_ebanx_payment_hash', $hash);
		update_post_meta(self::$post_id, '_ebanx_checkout_url', $url);
	}
}
