<?php

final class PluginSettingsChange extends Log {
	public static function persist(array $settingsData = []) {
		parent::save(
			'plugin_settings_change',
			array_merge(
		  		WC_EBANX_Log::get_platform_info(),
		  		[ 'settings' => $settingsData ]
		  	)
		);
	}
}
