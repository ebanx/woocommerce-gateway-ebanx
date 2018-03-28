<?php

if (!defined('ABSPATH')) {
	exit;
}

abstract class WC_EBANX_Helper
{
	/**
	 * Flatten an array
	 *
	 * @param  array  $array The array to flatten
	 * @return array        The new array flatted
	 */
	public static function flatten(array $array) {
		$return = array();
		array_walk_recursive($array, function($value) use (&$return) { $return[] = $value; });

		return $return;
	}

	/**
	 * Splits address in street name, house number and addition
	 *
	 * @param  string $address Address to be split
	 * @return array
	 */
	public static function split_street($address) {
		$result = preg_match('/^([^,\-\/\#0-9]*)\s*[,\-\/\#]?\s*([0-9]+)\s*[,\-\/]?\s*([^,\-\/]*)(\s*[,\-\/]?\s*)([^,\-\/]*)$/', $address, $matches);

		if ($result === false) {
			throw new \RuntimeException(sprintf('Problems trying to parse address: \'%s\'', $address));
		}

		if ($result === 0) {
			return array(
				'streetName' => $address,
				'houseNumber' => '',
				'additionToAddress' => ''
			);
		}

		$street_name = $matches[1];
		$house_number = $matches[2];
		$addition_to_address = $matches[3] . $matches[4] . $matches[5];

		if (empty($street_name)) {
			$street_name = $matches[3];
			$addition_to_address = $matches[5];
		}

		return array(
			'streetName' => $street_name,
			'houseNumber' => $house_number,
			'additionToAddress' => $addition_to_address
		);
	}

	/**
	 * Get post id from meta key and value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return int|bool
	 */
	public static function get_post_id_by_meta_key_and_value($key, $value) {
		global $wpdb;
		$meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".esc_sql($key)."' AND meta_value='".esc_sql($value)."'");
		if (is_array($meta) && !empty($meta) && isset($meta[0])) {
			$meta = $meta[0];
		}
		if (is_object($meta)) {
			return $meta->post_id;
		}
		else {
			return false;
		}
	}

	/**
	 * @param array $array
	 *
	 * @return object
	 */
	public static function array_to_object( $array ) {
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$array[ $key ] = static::array_to_object( $value );
			}
		}
		return (object) $array;
	}

	/*
	 * Verifies if user cart has any subscription product
	 *
	 * @return bool
	 */
	/**
	 * @return bool
	 */
	public static function checkout_contains_subscription() {
		global $product;
		if ( class_exists( 'WC_Subscription' ) ) {
			return WC_Subscriptions_Cart::cart_contains_subscription() || strpos( get_class( $product ), 'Subscription' ) !== false;
		}

		return false;
	}

}
