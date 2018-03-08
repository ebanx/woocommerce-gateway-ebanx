<?php 

final class WC_EBANX_Payment_By_Link_Logger extends WC_EBANX_Logger {
	public static function persist( array $logData = [] ) {
		parent::save('payment_by_link', $logData);
	}
}
