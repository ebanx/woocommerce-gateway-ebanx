<?php

/**
 * Abstract logger class, responsible to persist log on database
 */
abstract class WC_EBANX_Logger {
	/**
	 * method responsible to save log on database
	 *
	 * @param string $event data to be logged.
	 * @param array $log_data data to be logged.
	 */
	protected final static function save( $event, array $log_data ) {
		WC_EBANX_Database::insert( 'logs', array(
			'time' => current_time( 'mysql' ),
			'event' => $event,
			'log' => json_encode( $log_data ),
		));
	}

	/**
	 * Abstract method that must be overrated by child classes 
	 *
	 * This method is responsible for receive log data, manage them and send them to method save
	 *
	 * @param array $log_data data to be logged.
	 */
	abstract public static function persist( array $log_data = [] );
}
