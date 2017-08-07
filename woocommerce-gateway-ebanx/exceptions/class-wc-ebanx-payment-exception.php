<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Payment_Exception extends Exception {
	protected $code;
	protected $message;

	public function __construct($message, $code, Throwable $previous = null)
	{
		parent::__construct($code, 0, $previous);
		$this->code = $code;
		$this->message = $message;
	}
}
