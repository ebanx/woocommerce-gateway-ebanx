<?php

final class WC_EBANX_Notification_Query_Logger extends WC_EBANX_Logger {
	public static function persist( array $logData = [] ) {
		parent::save( 'notification_query', $logData );
	}
}
