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
	 * Cancels an open cash payment order with "On hold" status
	 *
	 * @return void
	 */
	public function cancel_order($order_id, $user_id) {
		$user_id = intval($user_id);
		$order_id = intval($order_id);
		$order = new WC_Order( $order_id );
		if ($user_id !== get_current_user_id()
		    || $order->get_status() !== 'on-hold'
		    || !in_array($order->get_payment_method(), WC_EBANX_Constants::$CASH_PAYMENTS)
			) {
				wp_redirect( get_site_url() );
				return;
		}

		$data = array(
			'hash' => get_post_meta($order_id, '_ebanx_payment_hash', true)
		);

		\Ebanx\Config::set(array(
			'integrationKey' => $this->get_integration_key(),
			'testMode'       => $this->is_sandbox(),
		));

		try {
			$request = \Ebanx\EBANX::doCancel($data);

			if ($request->status === 'SUCCESS') {
				$order->update_status('cancelled', 'Cancelled by customer');
				wp_redirect($order->get_view_order_url());
			}

		} catch (Exception $e) {
			return new WP_Error('ebanx_process_cancel_error', __('We could not cancel this order. Please try again.'));
		}
	}

	private function get_integration_key() {
		return $this->is_sandbox() ? $this->config->settings['sandbox_private_key'] : $this->config->settings['live_private_key'];
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

	/**
	 * @param $configs
	 *
	 * @return bool
	 */
	private function is_sandbox() {
		return $this->config->settings['sandbox_mode_enabled'] === 'yes';
	}
}
