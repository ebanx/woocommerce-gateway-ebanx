<?php

class NotificationQuery extends Log 
{
	public static function persist(array $logData = [])
	{
		parent::save(
			'notification_query',
			$logData
		);
	}
}
