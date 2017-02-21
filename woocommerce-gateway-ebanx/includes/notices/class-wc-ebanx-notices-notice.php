<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Notices_Notice {
	private $message;
	private $type = 'info';
	private $allowed_types = array(
			'error',
			'warning',
			'success',
			'info'
		);
	private $is_dismissible = true;
	private $view;

	public function __construct() {
		$args = func_get_args();
		switch (count($args)) {
			case 3:
				$this->is_dismissible = $args[2];
			case 2:
				$this->with_type($args[1]);
			case 1:
				$this->message = $args[0];
				break;
		}
	}

	public function with_view($view) {
		$this->view = $view;
		return $this;
	}

	public function with_message($message) {
		$this->message = $message;
		return $this;
	}

	public function with_type($type) {
		if (!in_array($type, $this->allowed_types)) {
			throw new InvalidArgumentException("Unknown notice type");
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
		if (isset($this->view)) {
			include INCLUDES_DIR . 'admin/views/html-notice-'.$this->view.'.php';
			return;
		}
		if (is_null($this->message)) {
			throw new Exception("You need to specify a message");
		}
		add_action('admin_notices', function () {
			$classes = "notice notice-{$this->type}";
			if ($this->is_dismissible) {
				$classes .= ' is-dismissible';
			}
			$notice = "<div class='$classes'><p>{$this->message}</p></div>";
			echo $notice;
		});
	}
}