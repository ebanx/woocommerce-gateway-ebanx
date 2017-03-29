<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class WC_EBANX_Payment_By_Link {

	private $errors = array();
	private $post_id;
	private $order;

	public function __construct($post_id){
		$this->post_id = $post_id;
		$this->order = wc_get_order($post_id);

		if ( ! $this->can_create_payment() ) {
			return;
		}

		if ( $this->validate() ) {
			$this->send_errors($this->errors);
		}

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
		if ( ! in_array(strtolower($this->order->billing_country), WC_EBANX_Gateway_Utils::$ALL_COUNTRIES) ) {
			$this->errors[] = 'EBANX only support the countries: Brazil, Mexico, Peru, Colombia and Chile. Please, use one of these.';
		}
		if ( ! array_key_exists($this->order->payment_method, WC_EBANX_Gateway_Utils::$GATEWAY_TO_PAYMENT_TYPE_CODE) ) {
			$this->errors[] = 'EBANX does not support the selected payment method.';
		}
		if ( empty($this->order->billing_email) ) {
			$this->errors[] = 'The customer e-mal is required, please provide a valid customer e-mail.';
		}
		if ( $this->order->payment_method !== ''
			&& array_key_exists(strtolower($this->order->billing_country), WC_EBANX_Gateway_Utils::$EBANX_GATEWAYS_BY_COUNTRY)
			&& ! in_array($this->order->payment_method, WC_EBANX_Gateway_Utils::$EBANX_GATEWAYS_BY_COUNTRY[strtolower($this->order->billing_country)]) ) {
			$this->errors[] = 'The selected payment method is not available on the selected country.';
		}
		return count($this->errors);
	}

	private function send_errors($errors) {
		//TODO: MAKE IT WORK
		$this->notices = new WC_EBANX_Notices_Notice();
			foreach ($errors as $error) {
				$this->notices
					->with_message(__($error, 'woocommerce-gateway-ebanx'))
					->with_type('error')
					->persistent()
					->display();
			}
			exit;
	}
}
