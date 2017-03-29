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

		$this->validate();

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
		if ( $this->order->get_total() < 1 ) {
			$this->errors[] = 'The total amount needs to be greater than $1.';
		}
		if ( $this->order->status === 'pending' ) {
			$this->errors[] = 'You can only create payment links on pending orders.';
		}
		if ( empty($this->order->billing_first_name) || empty($this->order->billing_last_name) ) {
			$this->errors[] = 'The customer name is required, please provide a valid customer name and last name.';
		}
		if ( ! in_array(strtolower($this->order->billing_country), WC_EBANX_Gateway_Utils::$ALL_COUNTRIES) ) {
			$this->errors[] = 'EBANX only support the countries: Brazil, Mexico, Peru, Colombia and Chile. Please, use one of these.';
		}
		if ( ! array_key_exists($this->order->billing_country, WC_EBANX_Gateway_Utils::$GATEWAY_TO_PAYMENT_TYPE_CODE) ) {
			$this->errors[] = 'EBANX does not support this payment type.';
		}
	}
}
