<?php 

class NotificationReceived extends Log
{
	public static function persist(array $logData = [])
	{
		parent::save(
			'notification',
			array_merge(
				WC_EBANX_Log::get_platform_info(),
		  		$logData
			)
		);
	}
}
