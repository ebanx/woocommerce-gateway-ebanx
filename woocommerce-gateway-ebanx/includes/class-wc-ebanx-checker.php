<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Checker {

	/**
	* Checks if sandbox mode is enabled and warns the user if it is.
	*
	* @return void
	*/
	public static function check_sandbox_mode($context) {

		if (!$context->is_sandbox_mode) {
			return;
		}

		$warning_message = __('EBANX Gateway - The Sandbox Mode option is enabled, in this mode, none of your transactions will be processed.', 'woocommerce-gateway-ebanx');
		$context->notices
			->with_message($warning_message)
			->with_type('warning')
			->persistent()
			->display();
	}
}