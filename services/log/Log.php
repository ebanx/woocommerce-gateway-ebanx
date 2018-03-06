<?php

abstract class Log {
	protected final static function save($event, array $logData) {
		WC_EBANX_Database::insert('logs', array(
			'time' => current_time( 'mysql' ),
			'event' => $event,
			'log' => json_encode($logData),
		));
	}

	public abstract static function persist(array $logData = []);
}
