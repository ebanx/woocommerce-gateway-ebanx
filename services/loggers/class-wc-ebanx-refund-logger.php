<?php

use EBANX\Plugin\Services\WC_EBANX_Log;

/**
 * Log Refund event data
 */
final class WC_EBANX_Refund_Logger extends WC_EBANX_Logger {
	/**
	 *
	 * @param array $log_data data to be logged.
	 * @param string $event event name to be logged.
	 */
	public static function persist(array $log_data = [], $event = 'refund') {
		parent::save(
			$event,
			array_merge(
				WC_EBANX_Log::get_platform_info(),
				$log_data
			)
		);
	}
}
