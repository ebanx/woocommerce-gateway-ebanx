<?php 

final class WC_EBANX_Payment_By_Link_Logger extends WC_EBANX_Logger {
	public static function persist( array $log_data = [] ) {
		parent::save( 'payment_by_link', $log_data );
	}
}
