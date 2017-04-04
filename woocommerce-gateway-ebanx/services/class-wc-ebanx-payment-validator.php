<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class WC_EBANX_Payment_Validator {

	private $errors = array();

	private $order;

	/**
	 * Construct
	 */
	public function __construct($order) {
		$this->order = $order;
	}

	/**
	 * Check if the error is not already in the array and add it.
	 * To make sure it will show no duplicates.
	 *
	 * @param string $error The error message
	 */
	private function add_error($error) {
		if ( ! in_array($error, $this->errors) ) {
			$this->errors[] = $error;
		}
	}

	/**
	 * Gets the error array
	 *
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Gets the error count
	 *
	 * @return int
	 */
	public function get_error_count() {
		return count($this->errors);
	}

	/**
	 * Apply all the input validation we need before sending the request
	 *
	 * @return int The number of errors it found
	 */
	public function validate() {
		if ($this->validate_status()) return true;
		$this->validate_currency();
		$this->validate_amount();
		$this->validate_country();
		$this->validate_email();
		if ($this->validate_payment_method()) return true;

		return $this->get_error_count() > 0;
	}

	/**
	 * Validates the order status
	 * @return bool Problems found
	 */
	private function validate_status() {
		if ( ! $this->order->status === 'pending' ) {
			$this->add_error(__('You can only create payment links on pending orders.', 'woocommerce-gateway-ebanx'));
			return true;
		}
		return false;
	}

	/**
	 * Validates the currency code
	 * @return bool Problems found
	 */
	private function validate_currency() {
		if ( get_woocommerce_currency() !== 'USD'
			&& get_woocommerce_currency() !== 'EUR'
			&& get_woocommerce_currency() !== WC_EBANX_Constants::$LOCAL_CURRENCIES[strtolower($this->order->billing_country)] ) {
			$this->add_error(sprintf(__('We can\'t proccess %s in the selected country.', 'woocommerce-gateway-ebanx'), get_woocommerce_currency()));
			return true;
		}

		return false;
	}

	/**
	 * Validates the order amount
	 * @return bool Problems found
	 */
	private function validate_amount() {
		if ( $this->order->get_total() < 1 ) {
			$this->add_error(__('The total amount needs to be greater than $1.', 'woocommerce-gateway-ebanx'));
			return true;
		}

		return false;
	}

	/**
	 * Validates the order country
	 * @return bool Problems found
	 */
	private function validate_country() {
		if ( ! in_array(strtolower($this->order->billing_country), WC_EBANX_Constants::$ALL_COUNTRIES) ) {
			$this->add_error(__('EBANX only support the countries: Brazil, Mexico, Peru, Colombia and Chile. Please, use one of these.', 'woocommerce-gateway-ebanx'));
			return true;
		}
		return false;
	}

	/**
	 * Validates the customer e-mail
	 * @return bool Problems found
	 */
	private function validate_email() {
		if ( ! filter_var($this->order->billing_email, FILTER_VALIDATE_EMAIL) ) {
			$this->add_error(__('The customer e-mal is required, please provide a valid customer e-mail.', 'woocommerce-gateway-ebanx'));
			return true;
		}

		return false;
	}

	/**
	 * Validates the payment method
	 * @return bool Problems found
	 */
	private function validate_payment_method() {
		if ( empty($this->order->payment_method) ) {
			return false;
		}

		// true: Leave and stop validating other fields
		if ($this->validate_payment_method_is_ebanx_account()) return true;

		if ($this->validate_payment_method_is_ebanx_payment()) return false;

		$this->validate_payment_method_country();

		return false;
	}

	/**
	 * Validates the payment method not to be ebanx-account
	 * As it's not available yet
	 *
	 * @return bool Problems found
	 */
	private function validate_payment_method_is_ebanx_account() {
		if ( $this->order->payment_method === 'ebanx-account' ) {
			$this->add_error(__('Paying with EBANX account is not avaible yet.', 'woocommerce-gateway-ebanx'));
			return true;
		}

		return false;
	}

	/**
	 * Validates the payment method to be on of ours
	 *
	 * @return bool Problems found
	 */
	private function validate_payment_method_is_ebanx_payment() {
		if ( ! array_key_exists($this->order->payment_method, WC_EBANX_Constants::$GATEWAY_TO_PAYMENT_TYPE_CODE) ) {
			$this->add_error(__('EBANX does not support the selected payment method.', 'woocommerce-gateway-ebanx'));
			return true;
		}

		return false;
	}

	/**
	 * Validates the payment method to match the selected country
	 *
	 * @return bool Problems found
	 */
	private function validate_payment_method_country() {
		if ( array_key_exists(strtolower($this->order->billing_country), WC_EBANX_Constants::$EBANX_GATEWAYS_BY_COUNTRY)
			&& ! in_array($this->order->payment_method, WC_EBANX_Constants::$EBANX_GATEWAYS_BY_COUNTRY[strtolower($this->order->billing_country)]) ) {
			$this->add_error(__('The selected payment method is not available on the selected country.', 'woocommerce-gateway-ebanx'));
			return true;
		}

		return false;
	}
}
