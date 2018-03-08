<?php

/**
 * Abstract logger class, responsible to persist log on database
 */
abstract class WC_EBANX_Logger {
	/**
	 * method responsible to save log on database
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
	 */
	public abstract static function persist( array $log_data = [] );
}
