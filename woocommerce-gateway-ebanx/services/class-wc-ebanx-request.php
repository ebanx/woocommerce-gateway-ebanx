<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Request {
	const DEFAULT_VALUE = 'WC_EBANX_Request::DEFAULT_VALUE';

	/**
	 * Reads _REQUEST and gets the value from '$param'
	 *
	 * @param  string $param   The key from _REQUEST
	 * @param  mixed $default What you want to return if there's no $_REQUEST[$param]
	 * @return mixed          $_REQUEST[$param] value OR default OR
	 * @throws Exception Throws exception if there's no $_REQUEST[$param] and no $default
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
	 * Reads the request body
	 */
	public static function read_body() {
		return file_get_contents('php://input');
	}

	/**
	 * Sets $value in $_REQUEST[$param]
	 *
	 * @param string $param The key from _REQUEST
	 * @param mixed $value The value you want to set
	 * @return void
	 */
	public static function set($param, $value) {
		$_REQUEST[$param] = $value;
	}

	/**
	 * Reads all keys in array $params from $_REQUEST
	 *
	 * @param  array $params  Array of strings, the values you want to read
	 * @param  mixed $default What you want to return if there's no value in each $_REQUEST[$params[]]
	 * @return array          An array with results OR
	 * @throws Exception Throws exception if there's no $_REQUEST[$param] and no $default
	 */
	public static function read_values($params, $default=self::DEFAULT_VALUE) {
		return array_map(function($param) use ($default) {
			$local_default = is_array($default) ? $default[$param] : $default;
			return self::read($param, $local_default);
		}, $params);
	}

	/**
	 * Check if a key is set on $_REQUEST
	 *
	 * @param  string  $param The key you want to check
	 * @return boolean        True if $_REQUEST has $key key
	 */
	public static function has($key) {
		return array_key_exists($key, $_REQUEST);
	}

	/**
	 * Gets the http method used in the request
	 *
	 * @return string
	 */
	public static function get_method() {
		return getenv('REQUEST_METHOD');
	}

	/**
	 * Gets the http request origin
	 *
	 * @return string
	 */
	public static function get_origin() {
		return isset($_SERVER['HTTP_ORIGIN'])
			? $_SERVER['HTTP_ORIGIN']
			: '';
	}

	/**
	 * Restricts the request origin
	 *
	 * @param  array $origins List of allowed origins
	 * @return void
	 */
	public static function restrict_origin($origins) {
		// Oh cache, why do you work too well?
		header('Vary: Origin');

		if ( in_array(self::get_origin(), $origins) ) {
			header('Access-Control-Allow-Origin: '.self::get_origin());
			return;
		}

		header('Access-Control-Allow-Origin: none');
		die('Thou shall not pass!');
	}

	/**
	 * Checks if $_GET is empty.
	 * Necessary for routers.
	 *
	 * @return boolean True if $_GET is empty
	 */
	public static function is_get_empty() {
		return empty($_GET);
	}

	/**
	 * Checks if $_POST is empty.
	 * Necessary for routers.
	 *
	 * @return boolean True if $_POST is empty
	 */
	public static function is_post_empty() {
		return empty($_POST);
	}
}
