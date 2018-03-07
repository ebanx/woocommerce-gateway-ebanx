<?php 

class Refund extends Log {
	public static function persist(array $logData = [])
	{
		parent::save(
			'refund',
			array_merge(
		  		WC_EBANX_Log::get_platform_info(),
		  		$logData
		  	)
		);
	}
}
