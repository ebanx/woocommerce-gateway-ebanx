<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class WC_EBANX_Payment_By_Link {

	private $errors = array();
	private $post_id;
	private $order;
	private $config;

	public function __construct($post_id, $config){
		$this->post_id = $post_id;
		$this->order = wc_get_order($post_id);
		$this->config = $config;

		if ( ! $this->can_create_payment() ) {
			return;
		}

		if ( $this->validate() ) {
			$this->send_errors();
			return;
		}

		$request = $this->send_request();
		if ( $request && $request->status !== 'SUCCESS' ) {
			$this->errors[] = 'We couldn\'t create your EBANX order. Could you review your fields and try again?';
			$this->send_errors();
			return;
		}

		$this->post_request($request->payment->hash, $request->redirect_url);
	}

	/**
	 * Checks if user has permissions to save data.
	 * AND Checks if not an autosave.
	 * AND Checks if not a revision.
	 *
	 * @return bool Can we create a payment by link now?
	 */
	private function can_create_payment() {
		return current_user_can( 'edit_post', $this->post_id )
				&& ! wp_is_post_autosave( $this->post_id )
				&& ! wp_is_post_revision( $this->post_id );
	}

	private function validate() {
		if ( ! $this->order->status === 'pending' ) {
			$this->errors[] = 'You can only create payment links on pending orders.';
			return count($this->errors);
		}
		if ( $this->order->get_total() < 1 ) {
			$this->errors[] = 'The total amount needs to be greater than $1.';
		}
		if ( ! in_array(strtolower($this->order->billing_country), WC_EBANX_Constants::$ALL_COUNTRIES) ) {
			$this->errors[] = 'EBANX only support the countries: Brazil, Mexico, Peru, Colombia and Chile. Please, use one of these.';
		}
		if ( ! array_key_exists($this->order->payment_method, WC_EBANX_Constants::$GATEWAY_TO_PAYMENT_TYPE_CODE) ) {
			$this->errors[] = 'EBANX does not support the selected payment method.';
		}
		if ( empty($this->order->billing_email) ) {
			$this->errors[] = 'The customer e-mal is required, please provide a valid customer e-mail.';
		}
		if ( $this->order->payment_method !== ''
			&& array_key_exists(strtolower($this->order->billing_country), WC_EBANX_Constants::$EBANX_GATEWAYS_BY_COUNTRY)
			&& ! in_array($this->order->payment_method, WC_EBANX_Constants::$EBANX_GATEWAYS_BY_COUNTRY[strtolower($this->order->billing_country)]) ) {
			$this->errors[] = 'The selected payment method is not available on the selected country.';
		}
		return count($this->errors);
	}

	private function send_errors() {
			foreach ($this->errors as $error) {
				WC_EBANX_Flash::add_message(__($error, 'woocommerce-gateway-ebanx'));
			}
	}

	private function send_request() {
		$data = array(
			'name'                  => $this->order->billing_first_name . ' ' . $this->order->billing_last_name,
			'email'                 => $this->order->billing_email,
			'country'               => strtolower($this->order->billing_country),
			'payment_type_code'     => empty($this->order->payment_method) ? '_all' : WC_EBANX_Constants::$GATEWAY_TO_PAYMENT_TYPE_CODE[$this->order->payment_method],
			'merchant_payment_code' => $this->order->id . '_' . md5(time()),
			'currency_code'         => strtoupper(get_woocommerce_currency()),
			'amount'                => $this->order->get_total()
		);

		\Ebanx\Config::set($this->config);
		\Ebanx\Config::setDirectMode(false);

		$request = false;

		try {
			$request = \Ebanx\EBANX::doRequest($data);
		} catch (Exception $e) {
			$this->errors[] = $e->getMessage();
			$this->send_errors();
		}

		return $request;
	}

	private function post_request($hash, $url) {
		$this->order->update_status('on-hold', __('Order created via EBANX.', 'woocommerce-gateway-ebanx'));
		update_post_meta($this->post_id, '_ebanx_payment_hash', $hash);
		update_post_meta($this->post_id, '_ebanx_checkout_url', $url);
	}
}
