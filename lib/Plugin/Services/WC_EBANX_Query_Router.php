<?php

namespace EBANX\Plugin\Services;

include_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-request.php';

class WC_EBANX_Query_Router {
	private $key = null;
	private $routes = array();

	public function __construct( $key ) {
		self::validate_key( $key );
		$this->key = $key;
	}

	public function map( $key_value, $handler ) {
		self::validate_key_value( $key_value );
		self::validate_route( $handler );

		$this->routes[ $key_value ] = $handler;
	}

	public function serve() {
		$route = $this->find_route_for_key();
		if ( null === $route ) {
			return;
		}

		$params = self::read_request_for_route( $route );

		call_user_func_array( $route, $params );
		exit;
	}

	private function find_route_for_key( $search_key = null ) {
		$key = $this->key;
		if ( null !== $search_key ) {
			$key = $search_key;
		}

		self::validate_key( $key );

		if ( ! \WC_EBANX_Request::has( $key ) ) {
			return null;
		}

		$value = \WC_EBANX_Request::read( $key );

		self::validate_key_value( $value );

		if ( ! isset( $this->routes[ $value ] ) ) {
			return null;
		}

		return $this->routes[ $value ];
	}

	private static function read_request_for_route( $route ) {
		self::validate_route( $route );

		$ref  = new \ReflectionMethod( $route[0], $route[1] );
		$list = $ref->getParameters();

		$params = array();
		foreach ( $list as $arg ) {
			$name = $arg->name;
			if ( ! \WC_EBANX_Request::has( $name ) ) {
				$params[ $name ] = null;
				continue;
			}

			$params[ $name ] = \WC_EBANX_Request::read( $name );
		}

		return $params;
	}

	private static function validate_key( $subject ) {
		if ( ! is_string( $subject ) ) {
			throw new \InvalidArgumentException( 'A query route key must be a string!' );
		}
	}

	private static function validate_key_value( $subject ) {
		if ( ! is_string( $subject ) ) {
			throw new \InvalidArgumentException( 'A query route key value must be a string!' );
		}
	}

	private static function validate_route( $subject ) {
		if ( ! is_callable( $subject ) ) {
			throw new \InvalidArgumentException( 'Specified route is not callable!' );
		}
	}
}
