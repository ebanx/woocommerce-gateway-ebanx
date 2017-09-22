<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Multicaja_Gateway extends WC_EBANX_Flow_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id = 'ebanx-multicaja';
		$this->method_title = __('EBANX - Multicaja', 'woocommerce-gateway-ebanx');

		$this->title = 'Multicaja';
		$this->description = 'Paga con multicaja.';

		$this->template_file = 'flow/multicaja/payment-form.php';
		$this->flow_payment_method = 'multicaja';

		parent::__construct();
	}
}
