<?php

final class WX_EBANX_Plugin_Deactivate_Logger extends WC_EBANX_Logger {
	public static function persist(array $logData = []) {
		parent::save(
			'plugin_deactivate',
			array_merge(
		  		WC_EBANX_Log::get_platform_info(),
		  		$logData
		  	)
		);
	}
}
