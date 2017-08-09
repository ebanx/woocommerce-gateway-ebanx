<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

class WC_EBANX_Api_Controller {
	private $config;

	/**
	 * Construct the controller with our config object
	 *
	 * @param WC_EBANX_Global_Gateway $config
	 */
	public function __construct(WC_EBANX_Global_Gateway $config) {
		$this->config = $config;
	}

	/**
	 * Responds that the plugin is installed
	 *
	 * @return void
	 */
	public function dashboard_check() {
		echo json_encode(array(
			'ebanx' => true,
			'version' => WC_EBANX::get_plugin_version()
		));
	}

	/**
	 * Responds that the plugin is installed
	 *
	 * @return void
	 */
	public function cancel_order() {
		echo 'cancelado';
	}

	/**
	 * Gets the banking ticket HTML by cUrl with url fopen fallback
	 *
	 * @return void
	 */
	public function order_received($hash, $payment_type) {
		$is_sandbox = $this->config->get_setting_or_default('sandbox_mode_enabled', 'yes') === 'yes';

		$subdomain = $is_sandbox ? 'sandbox' : 'print';
		$url = "https://{$subdomain}.ebanx.com/";

		if ($payment_type !== 'cip') {
			$url .= 'print/';
		}

		if ($payment_type !== null && $payment_type !== 'boleto') {
			$url .= "{$payment_type}/";
		}

		$url .= "?hash={$hash}";

		if ($payment_type !== 'baloto') {
			$url .= '&format=basic#';
		}

		if (!in_array('curl', get_loaded_extensions())) {
			echo file_get_contents($url);
			return;
		}

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$html = curl_exec($curl);

		if (curl_error($curl)) {
			return;
		}

		curl_close($curl);
		echo $html;
	}
}
