<?php

use EBANX\Plugin\Services\WC_EBANX_Log;

/**
 * Log Checkout event data
 */
final class WC_EBANX_Checkout_Logger extends WC_EBANX_Logger {
	/**
	 *
	 * @param array $log_data data to be logged.
	 * @param string $event event name to be logged.
	 */
	public static function persist( array $log_data = [], $event = null) {
		parent::save(
			'checkout',
			array_merge(
				WC_EBANX_Log::get_platform_info(),
				$log_data
			)
		);
	}
}
