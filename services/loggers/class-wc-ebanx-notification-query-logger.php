<?php

/**
 * Log Notification Query event data
 */
final class WC_EBANX_Notification_Query_Logger extends WC_EBANX_Logger {
	/**
	 *
	 * @param array $log_data data to be logged.
	 * @param string $event event name to be logged.
	 */
	public static function persist( array $log_data = [], $event = null) {
		parent::save($event, $log_data);
	}
}
