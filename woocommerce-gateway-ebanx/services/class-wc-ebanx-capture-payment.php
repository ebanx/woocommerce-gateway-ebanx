<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Capture_Payment {
	public static function add_order_capture_button( $actions, $order ) {
		if ($order->get_status() !== 'on-hold' && ) {
			return $actions;
		}

		$actions['capture'] = array(
			'url'    => static::get_capture_button_url($order),
			'name'   => __( 'Capture payment', 'woocommerce-gateway-ebanx' ),
			'action' => "view capture",
		);

		return $actions;
	}

	private static function get_capture_button_url($order) {
		return get_admin_url() . '?ebanx=capture-payment&order_id=' . $order->get_id();
	}

	public static function add_order_capture_button_css() {
		echo '<style>.view.capture::after { font-family: woocommerce; content: "\e005" !important; }</style>';
	}
}
