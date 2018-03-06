<?php

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

/**
 * Class WC_EBANX_Database
 */
class WC_EBANX_Database {
	public static function tables() {
		global $wpdb;

		return [
			'logs' => $wpdb->prefix . 'ebanx_logs',
		];
	}

	public static function migrate() {
		self::create_log_table();
	}

	private static function create_log_table() {
		global $wpdb;

		$table_name = self::tables()['logs'];
		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table_name ) ) ) {
			return;
		}

		$sql = "CREATE TABLE $table_name (
			id int NOT NULL AUTO_INCREMENT,
			time datetime NOT NULL,
			event varchar(150) NOT NULL,
			log blob NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate";

		dbDelta( $sql );
	}

	public static function insert( $table, $data ) {
		global $wpdb;

		return $wpdb->insert( self::tables()[$table], $data);
	}

	public static function truncate( $table ) {
		global $wpdb;

		$wpdb->query("TRUNCATE TABLE " . self::tables()[$table]);
	}

	public static function select( $table ) {
		global $wpdb;

		return $wpdb->get_results("SELECT * FROM " . self::tables()[$table]);
	}
}
