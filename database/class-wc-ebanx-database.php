<?php

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

/**
 * Class WC_EBANX_Database
 */
class WC_EBANX_Database {

	/**
	 * Cretaes EBANX logs table on database.
	 */
	public function create_log_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ebanx_logs';
		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table_name ) ) ) {
			return;
		}

		$sql = "CREATE TABLE $table_name (
			id int NOT NULL AUTO_INCREMENT,
			time datetime NOT NULL,
			event varchar(15) NOT NULL,
			log blob NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate";

		dbDelta( $sql );
	}
}
