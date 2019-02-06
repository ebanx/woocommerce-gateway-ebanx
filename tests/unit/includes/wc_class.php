<?php

class WC_Payment_Gateway {
	function init_settings() {
		$this->settings = [];
	}
}

class Wordpress {
	function db_version() {
		return '1.0.2';
	}
}

$wpdb = new Wordpress();
