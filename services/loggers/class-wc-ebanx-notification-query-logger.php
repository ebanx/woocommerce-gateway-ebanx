<?php

/**
 * Log Notification Query event data
 */
final class WC_EBANX_Notification_Query_Logger extends WC_EBANX_Logger {
	/**
	 * @inheritdoc
	 */
	public static function persist( array $log_data = [] ) {
		parent::save( 'notification_query', $log_data );
	}
}
