<?php

namespace EBANX\Plugin\Services;

class WC_EBANX_Notice {

	private $message;
	private $type = 'info';
	private $is_dismissible = true;
	private $view;
	private $allowed_types = array(
		'error',
		'warning',
		'success',
		'info',
	);

	public function __construct() {
		$args = func_get_args();
		switch ( count( $args ) ) {
			case 3:
				$this->is_dismissible = $args[2];
			// FALLTHROUGH.
			case 2:
				$this->with_type( $args[1] );
			// FALLTHROUGH.
			case 1:
				$this->message = $args[0];
				break;
		}
	}

	public function with_view( $view ) {
		$this->view = $view;
		return $this;
	}

	public function with_message( $message ) {
		$this->message = $message;
		return $this;
	}

	public function with_type( $type ) {
		if ( ! in_array( $type, $this->allowed_types ) ) {
			throw new \InvalidArgumentException( 'Unknown notice type' );
		}
		$this->type = $type;
		return $this;
	}

	public function dismissible() {
		$this->is_dismissible = true;
		return $this;
	}

	public function persistent() {
		$this->is_dismissible = false;
		return $this;
	}

	public function enqueue() {
		if ( isset( $this->view ) ) {
			$view = $this->view;
			add_action(
				'admin_notices', function () use ( $view ) {
				include WC_EBANX_TEMPLATES_DIR . 'views/html-notice-' . $view . '.php';
			}
			);
			$this->view = null;
			return $this;
		}
		if ( is_null( $this->message ) ) {
			throw new Exception( 'You need to specify a message' );
		}
		$type           = $this->type;
		$message        = $this->message;
		$is_dismissible = $this->is_dismissible;
		add_action(
			'admin_notices', function () use ( $type, $message, $is_dismissible ) {
			$classes = "notice notice-{$type}";
			if ( $is_dismissible ) {
				$classes .= ' is-dismissible';
			}
			$notice = "<div class='$classes'><p>{$message}</p></div>";
			echo $notice;
		}
		);
		return $this;
	}

	public function display() {
		if ( isset( $this->view ) ) {
			$view = $this->view;
			include WC_EBANX_TEMPLATES_DIR . 'views/html-notice-' . $view . '.php';
			$this->view = null;
			return $this;
		}
		if ( is_null( $this->message ) ) {
			throw new \Exception( 'You need to specify a message' );
		}
		$type           = $this->type;
		$message        = $this->message;
		$is_dismissible = $this->is_dismissible;
		$classes        = "notice notice-{$type}";
		if ( $is_dismissible ) {
			$classes .= ' is-dismissible';
		}
		$notice = "<div class='$classes'><p>{$message}</p></div>";
		echo $notice;
		return $this;
	}
}
