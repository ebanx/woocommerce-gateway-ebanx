<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Cancel_Order {
	public static function add_my_account_cancel_order_action( $actions, $order ) {
		if ( 'on-hold' !== $order->get_status() || ! in_array( $order->get_payment_method(), WC_EBANX_Constants::$cash_payment_gateways_code ) ) {
			return $actions;
		}

		$actions['cancel'] = array(
			'url'  => self::get_cancel_button_url($order),
			'name' => __('Cancel', 'woocommerce-gateway-ebanx'),
		);
		return $actions;
	}

	private static function get_cancel_button_url($order) {
		return get_site_url() . '?ebanx=cancel-order&user_id=' . $order->get_user_id() . '&order_id=' . $order->get_id();
	}
}
