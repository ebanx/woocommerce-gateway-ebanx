<?php 

class Checkout extends Log
{
	public static function persist(array $logData = [])
	{
		parent::save(
			'checkout',
			array_merge(
				WC_EBANX_Log::get_platform_info(),
		  		$logData
			)
		);
	}
}
