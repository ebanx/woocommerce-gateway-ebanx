<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

class WC_EBANX_Integration_Controller {
	private $config;
	private $lead_id;

	/**
	 * Construct the controller with our config object
	 *
	 * @param WC_EBANX_Global_Gateway $config
	 */
	public function __construct(WC_EBANX_Global_Gateway $config) {
		$this->config = $config;

		$this->lead_id = get_option('_ebanx_lead_id');
	}

	/**
	 * Responds to the fetch keys action with an external url using our lead id
	 *
	 * @return void
	 */
	public function fetch_keys_modal() {
		$lead_id = $this->lead_id;
		//$url = "http://everest.ebanx.com/api/fetch_keys/{$lead_id}";
		$url = "http://dev-everest.ebanx.com/api/fetch_keys/c11e2065-9023-4d19-84d3-2fb86491670a";
		echo '<img style="display: block; margin: 15% auto -100px auto; width: 400px; height: 300px;" src="'.WC_EBANX_Assets::get_logo().'" />';
		echo '<p style="display: block; text-align: center; font-family: sans-serif; font-size: 24px;"><img src="'.esc_url( admin_url('/images/spinner-2x.gif') ).'" width="20" height="20" /> The world is yours.</p>';
		echo '<script type="text/javascript">setTimeout(function(){ document.location.href = "'.$url.'"; }, 300);</script>';
	}

	/**
	 * Sets the integration keys for this lead when asked by dashboard
	 *
	 * @param  string $lead_id The activation key
	 * @return void
	 */
	public function write_keys($lead_id) {
		$allow_from = array(
			'https://dashboard.ebanx.com',
			'http://dev-everest.ebanx.com',
			'http://localhost:8089'
		);
		WC_EBANX_Request::restrict_origin($allow_from);

		// Invalid
		if ($this->lead_id !== $lead_id) {
			return;
		}

		$data = json_decode(WC_EBANX_Request::read_body());

		$key_pair_schema = array(
			"public" => "string",
			"private" => "string"
		);
		$schema = array(
			"ebanx_integration" => array(
				"live" => $key_pair_schema,
				"sandbox" => $key_pair_schema
			)
		);

		// Also invalid
		if ( ! WC_EBANX_Helper::match_schema($data, $schema) ) {
			return;
		}

		$keys = $data->ebanx_integration;

		$this->config->settings['sandbox_private_key'] = $keys->sandbox->private;
		$this->config->settings['sandbox_public_key'] = $keys->sandbox->public;
		$this->config->settings['live_private_key'] = $keys->live->private;
		$this->config->settings['live_public_key'] = $keys->live->public;

		$this->config->save_current_settings();
	}
}
