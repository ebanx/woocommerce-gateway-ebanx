<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Request {
	const DEFAULT_VALUE = 'WC_EBANX_Request::DEFAULT_VALUE';

	/**
	 * [read description]
	 *
	 * @param  string $param   [description]
	 * @param  mixed $default [description]
	 * @return mixed          [description]
	 * @throws Exception [<description>]
	 */
	public static function read($param, $default=self::DEFAULT_VALUE) {
		if ( self::has($param) ) {
			return $_REQUEST[$param];
		}
		if ( $default == self::DEFAULT_VALUE ) {
			throw new Exception('Missing argument "'.$param.'".');
		}
		return $default;
	}

	/**
	 * [set description]
	 *
	 * @param string $param [description]
	 * @param mixed $value [description]
	 * @return void
	 */
	public static function set($param, $value) {
		$_REQUEST[$param] = $value;
	}

	/**
	 * [read_values description]
	 *
	 * @param  string $params  [description]
	 * @param  mixed $default [description]
	 * @return mixed          [description]
	 * @throws Exception [<description>]
	 */
	public static function read_values($params, $default=self::DEFAULT_VALUE) {
		return array_map(function($param) use ($default) {
			return self::read($param, $default[$param]);
		}, $params);
	}

	/**
	 * [has description]
	 *
	 * @param  string  $param [description]
	 * @return boolean        [description]
	 */
	public static function has($param) {
		return array_key_exists($param, $_REQUEST);
	}

	public static function is_get_empty() {
		return empty($_GET);
	}

	public static function is_post_empty() {
		return empty($_POST);
	}
}
