<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

class WC_EBANX_Query_Router {
	private $key = null;
	private $routes = array();

	/**
	 * Construct the router using a query string key
	 *
	 * @param string $key
	 */
	public function __construct($key) {
		self::validate_key($key);
		$this->key = $key;
	}

	/**
	 * Maps a key value to a handler method
	 *
	 * @param  string $key_value The value which triggers this route
	 * @param  array  $handler   A callable array with instance/class and method
	 * @return void
	 */
	public function map($key_value, $handler) {
		self::validate_key_value($key_value);
		self::validate_route($handler);

		$this->routes[$key_value] = $handler;
	}

	/**
	 * The dispatcher that will make coffee for you if you ask nicely
	 * Just call this at the end of your setup
	 *
	 * @return void
	 */
	public function serve() {
		$route = $this->find_route_for_key();
		if ( $route === null ) {
			// Not found, carry on
			return;
		}

		$params = self::read_request_for_route($route);

		// Route it and die
		call_user_func_array($route, $params);
		wp_die();
	}

	// Private

	/**
	 * Locates the desired route based on the key value
	 *
	 * @param  string $search_key A key to read the value from (optional)
	 * @return array              A callable array of your route handler
	 */
	private function find_route_for_key($search_key = null) {
		$key = $this->key;
		if ( $search_key !== null ) {
			$key = $search_key;
		}

		self::validate_key($key);

		if ( ! WC_EBANX_Request::has($key) ) {
			// Not set, not found
			return null;
		}

		$value = WC_EBANX_Request::read($key);

		self::validate_key_value($value);

		// Not found
		if ( ! isset($this->routes[$value]) ) {
			return null;
		}

		return $this->routes[$value];
	}

	/**
	 * This finds out which parameters are needed for your handler
	 * and assembles the parameter array
	 *
	 * @param  array $route Callable array
	 * @return array
	 */
	private static function read_request_for_route($route) {
		self::validate_route($route);

		$ref = new ReflectionMethod($route[0], $route[1]);
		$list = $ref->getParameters();

		$params = array();
		foreach($list as $arg) {
			$name = $arg->name;
			if ( ! WC_EBANX_Request::has($name) ) {
				$params[$name] = null;
				continue;
			}

			$params[$name] = WC_EBANX_Request::read($name);
		}

		return $params;
	}

	/**
	 * Validates a routing key
	 *
	 * @param  string $subject Routing key
	 * @return void
	 * @throws InvalidArgumentException
	 */
	private static function validate_key($subject) {
		if ( ! is_string($subject) ) {
			throw new InvalidArgumentException("A query route key must be a string!");
		}
	}

	/**
	 * Validates a query key value
	 *
	 * @param  string $subject Key value
	 * @return void
	 * @throws InvalidArgumentException
	 */
	private static function validate_key_value($subject) {
		if ( ! is_string($subject) ) {
			throw new InvalidArgumentException("A query route key value must be a string!");
		}
	}

	/**
	 * Validates a route handler
	 *
	 * @param  array $subject Route handler callable array
	 * @return void
	 * @throws InvalidArgumentException
	 */
	private static function validate_route($subject) {
		if ( ! is_callable($subject) ) {
			throw new InvalidArgumentException("Specified route is not callable!");
		}
	}
}
