<?php 

class PaymentByLink extends Log
{
	public static function persist(array $logData = [])
	{
		parent::save('payment_by_link', $logData);
	}
}
