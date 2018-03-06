<?php

final class PluginDeactivate extends Log {
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
