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
}
