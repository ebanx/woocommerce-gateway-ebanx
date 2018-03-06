<?php

final class PluginActivate extends Log {
	public static function persist(array $logData = []) {
		parent::save(
			'plugin_activate',
			array_merge(
		  		WC_EBANX_Log::get_platform_info(),
		  		$logData
		  	)
		);
	}
}
