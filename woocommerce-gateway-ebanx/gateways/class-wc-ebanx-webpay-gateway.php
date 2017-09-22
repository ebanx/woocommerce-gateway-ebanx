<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Webpay_Gateway extends WC_EBANX_Flow_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id = 'ebanx-webpay';
		$this->method_title = __('EBANX - Webpay', 'woocommerce-gateway-ebanx');

		$this->title = 'Webpay';
		$this->description = 'Paga con Webpay.';

		$this->template_file = 'flow/webpay/payment-form.php';
		$this->flow_payment_method = 'webpay';

		parent::__construct();
	}
}
