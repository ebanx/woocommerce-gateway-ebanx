<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_EBANX_Third_Party_Compability_Layer {
	public static function check_and_solve() {
		add_action( 'wp_enqueue_scripts', array( self::class, 'check_and_solve_sticky_checkout' ), 90 );
	}

	public static function check_and_solve_sticky_checkout() {
		self::solve_sticky_checkout_storefront();
	}

	private static function solve_sticky_checkout_storefront() {
		if ( wp_get_theme()->get( 'Name' ) === 'Storefront' ) {
			wp_deregister_script( 'storefront-sticky-payment' );
		}
	}
}
