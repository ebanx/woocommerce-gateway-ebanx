<?php 

final class WC_EBANX_Notification_Received_Logger extends WC_EBANX_Logger {
	public static function persist( array $log_data = [] ) {
		parent::save(
			'notification',
			array_merge(
				WC_EBANX_Log::get_platform_info(),
		  		$log_data
			)
		);
	}
}
