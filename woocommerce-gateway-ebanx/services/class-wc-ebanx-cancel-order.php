<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Cancel_Order {
	public static function add_my_account_cancel_order_action( $actions, $order ) {
		if ($order->get_status() !== 'on-hold' && !in_array($order->get_payment_method(), WC_EBANX_Constants::$CASH_PAYMENTS)) {
			return $actions;
		}

		$actions['cancel'] = array(
			'url'  => get_site_url() . '?ebanx=cancel-order',
			'name' => __('Cancel', 'woocommerce-gateway-ebanx'),
		);
		return $actions;
	}
}
