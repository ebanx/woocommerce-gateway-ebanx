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
	 * Captures a credit card payment made while auto capture was disabled
	 *
	 * @return void
	 */
	public function capture_payment($order_id) {
		$order = new WC_Order( $order_id );

		if (!current_user_can('administrator')
		    || $order->get_status() !== 'on-hold'
		    || strpos($order->get_payment_method(), 'ebanx-credit-card') !== 0
		) {
			wp_redirect( get_site_url() );
			return;
		}

		\Ebanx\Config::set(array(
			'integrationKey' => $this->get_integration_key(),
			'testMode'       => $this->is_sandbox(),
			'directMode' => true,
		));

		$response = \Ebanx\Ebanx::doCapture(array('hash' => get_post_meta($order_id, '_ebanx_payment_hash', true)));
		$error = $this->check_capture_errors($response);

		$is_recapture = false;
		if($error){
			$is_recapture = $error->code === 'BP-CAP-4';
			$response->payment->status = $error->status;

			WC_EBANX::log($error->message);
			WC_EBANX_Flash::add_message($error->message, 'warning', true);
		}
		if ($response->payment->status == 'CO') {
			$order->payment_complete();

			if (!$is_recapture) {
				$order->add_order_note(sprintf(__('EBANX: The transaction was captured with the following: %s', 'woocommerce-gateway-ebanx'), wp_get_current_user()->data->user_email));
				WC_EBANX_Flash::add_message(__('Payment ' . $order_id . ' was captured successfully.', 'woocommerce-gateway-ebanx'), 'warning', true);
			}
		}
	else if ($response->payment->status == 'CA') {
			$order->payment_complete();
			$order->update_status('failed');
			$order->add_order_note(__('EBANX: Transaction Failed', 'woocommerce-gateway-ebanx'));
		}
	else if ($response->payment->status == 'OP') {
			$order->update_status('pending');
			$order->add_order_note(__('EBANX: Transaction Pending', 'woocommerce-gateway-ebanx'));
		}
		wp_redirect($this->get_admin_order_url($order_id));
	}

	/**
	 * @return String
	 */
	public function get_admin_order_url($order_id) {
		return admin_url() . 'post.php?post=' . $order_id . '&action=edit';
	}

	/**
	 * Checks for errors during capture action
	 * Returns an object with error code, message and target status
	 *
	 * @param object $response The response from EBANX API
	 * @return stdClass
	 */
	public function check_capture_errors($response) {
		if ( $response->status !== 'ERROR' ) {
			return null;
		}

		$code = $response->code;
		$message = sprintf(__('EBANX - Unknown error, enter in contact with Ebanx and inform this error code: %s.', 'woocommerce-gateway-ebanx'), $response->payment->status_code);
		$status = $response->payment->status;

		switch($response->status_code) {
			case 'BC-CAP-3':
				$message = __('EBANX - Payment cannot be captured, changing it to Failed.', 'woocommerce-gateway-ebanx');
				$status = 'CA';
				break;
			case 'BP-CAP-4':
				$message = __('EBANX - Payment has already been captured, changing it to Processing.', 'woocommerce-gateway-ebanx');
				$status = 'CO';
				break;
			case 'BC-CAP-5':
				$message = __('EBANX - Payment cannot be captured, changing it to Pending.', 'woocommerce-gateway-ebanx');
				$status = 'OP';
				break;
		}

		return (object)array(
			'code' => $code,
			'message' => $message,
			'status' => $status
		);
	}

	/**
	 * Cancels an open cash payment order with "On hold" status
	 *
	 * @return void
	 */
	public function cancel_order($order_id, $user_id) {
		$order = new WC_Order( $order_id );
		if ($user_id != get_current_user_id()
		    || $order->get_status() !== 'on-hold'
		    || !in_array($order->get_payment_method(), WC_EBANX_Constants::$CASH_PAYMENTS_GATEWAYS_CODE)
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
				$order->update_status('cancelled', __('EBANX: Cancelled by customer', 'woocommerce-gateway-ebanx'));
			}

			wp_redirect($order->get_view_order_url());

		} catch (Exception $e) {
			$message = $e->getMessage();
			WC_EBANX::log("EBANX Error: $message");

			wc_add_notice($message, 'error');
			wp_redirect( get_site_url() );
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
		$url = $this->get_url($hash, $payment_type);

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

	private function get_url($hash, $payment_type) {
		$is_sandbox = $this->config->get_setting_or_default('sandbox_mode_enabled', 'yes') === 'yes';

		$subdomain = $is_sandbox ? 'sandbox' : 'print';
		$url = "https://{$subdomain}.ebanx.com/";

		if ($payment_type !== 'cip') {
			$url .= 'print/';
		}

		if ($payment_type === 'efectivo') {
			$url .= 'voucher/';
		}

		if ($payment_type !== null && $payment_type !== 'boleto' && $payment_type !== 'efectivo') {
			$url .= "{$payment_type}/";
		}

		$url .= "?hash={$hash}";

		if ($payment_type !== 'baloto') {
			$url .= '&format=basic#';
		}

		return $url;
	}
}
