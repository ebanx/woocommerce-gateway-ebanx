<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class WC_EBANX_Payment_By_Link {

	private static $errors = array();
	private static $post_id;
	private static $order;
	private static $config;
	private static $validator;

	/**
	 * The core method. It uses the other methods to create a payment link
	 *
	 * @param  int $post_id The post id
	 * @param  array $config  The config array we need to pass to ebanx-php
	 * @return void
	 */
	public static function create($post_id, $config){
		self::$post_id = $post_id;
		self::$order = wc_get_order($post_id);
		self::$config = $config;
		self::$validator = new WC_EBANX_Payment_Validator(self::$order);

		if ( ! self::can_create_payment() ) {
			return;
		}

		if ( self::$validator->validate() ) {
			self::$errors = array_merge(self::$errors, self::$validator->get_errors());
			self::send_errors();
			return;
		}

		$request = self::send_request();
		if ( $request && $request->status !== 'SUCCESS' ) {
			self::add_error(self::getErrorMessage($request));
			self::send_errors();
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
	private static function can_create_payment() {
		return current_user_can( 'edit_post', self::$post_id )
				&& ! wp_is_post_autosave( self::$post_id )
				&& ! wp_is_post_revision( self::$post_id );
	}

	/**
	 * Flashes every error from self::$errors to WC_EBANX_Flash
	 *
	 * @return void
	 */
	private static function send_errors() {
		WC_EBANX_Flash::clear_messages();
		foreach (array_unique(self::$errors) as $error) {
			WC_EBANX_Flash::add_message($error);
		}
	}

	/**
	 * Requests a payment link using ebanx-php
	 *
	 * @return void
	 */
	private static function send_request() {
		$data = array(
			'name'                  => self::$order->billing_first_name . ' ' . self::$order->billing_last_name,
			'email'                 => self::$order->billing_email,
			'country'               => strtolower(self::$order->billing_country),
			'payment_type_code'     => empty(self::$order->payment_method) ? '_all' : WC_EBANX_Constants::$GATEWAY_TO_PAYMENT_TYPE_CODE[self::$order->payment_method],
			'merchant_payment_code' => substr(self::$order->id . '_' . md5(time()), 0, 40),
			'currency_code'         => strtoupper(get_woocommerce_currency()),
			'amount'                => self::$order->get_total(),
			'instalments'           => self::get_instalments_range(),
			'user_value_1'          => 'from_woocommerce',
			'user_value_3'          => 'version=' . WC_EBANX::get_plugin_version(),
		);

		\Ebanx\Config::set(self::$config);
		\Ebanx\Config::setDirectMode(false);

		$request = false;

		try {
			$request = \Ebanx\EBANX::doRequest($data);
		} catch (Exception $e) {
			self::add_error($e->getMessage());
			self::send_errors();
		}

		return $request;
	}

	/**
	 * If the request was successful, this method is called before ending the proccess
	 *
	 * @param  string $hash The payment hash
	 * @param  string $url  The payment url
	 * @return void
	 */
	private static function post_request($hash, $url) {
		self::$order->add_order_note(__('EBANX: Your order was created via EBANX.', 'woocommerce-gateway-ebanx'));
		update_post_meta(self::$post_id, '_ebanx_payment_hash', $hash);
		update_post_meta(self::$post_id, '_ebanx_checkout_url', $url);
	}

	/**
	 * Check if the error is not already in the array and add it.
	 * To make sure it will show no duplicates.
	 *
	 * @param string $error The error message
	 */
	private static function add_error($error) {
		if ( ! in_array($error, self::$errors) ) {
			self::$errors[] = $error;
		}
	}

	private static function get_instalments_range() {
		return '1-'. get_post_meta(self::$order->id, '_ebanx_instalments', true);
	}

	/**
	 * @param $request
	 * @return string
	 */
	private static function getErrorMessage($request)
	{
		if ( WP_DEBUG ) {
			return $request->status_code . ': ' . $request->status_message;
		}

		switch ($request->status_code) {
			case 'BP-R-32':
				// Amount must be less than {currencyCode} {amount}
				$value = explode(' ', substr($request->status_message, 25));
				$currencyCode = $value[0];
				$amount = number_format( $value[1], 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );

				return sprintf(__("Your transaction's value must be lower than %s %s. Please, set a lower one."), $currencyCode, $amount);
			default:
				return __( 'We couldn\'t create your EBANX order. Could you review your fields and try again?' );
		}
	}
}
