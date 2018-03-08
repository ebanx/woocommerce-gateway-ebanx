<?php

final class WC_EBANX_Plugin_Settings_Change_Logger extends WC_EBANX_Logger {
	public static function persist( array $settingsData = [] ) {
		parent::save(
			'plugin_settings_change',
			array_merge(
		  		WC_EBANX_Log::get_platform_info(),
		  		[ 'settings' => $settingsData ]
		  	)
		);
	}
}
