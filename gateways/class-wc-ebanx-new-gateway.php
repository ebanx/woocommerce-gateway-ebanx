<?php

use Ebanx\Benjamin\Services\Gateways\Boleto;

require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-api.php';
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-payment-adapter.php';

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_New_Gateway extends WC_EBANX_Gateway
{
	/**
	 * @var int
	 */
	public $user_id;

	/**
	 * @var WC_EBANX_Global_Gateway
	 */
	protected $configs;

	/**
	 * @var bool
	 */
	protected $is_sandbox_mode;

	/**
	 * @var string
	 */
	protected $private_key;

	/**
	 * @var string
	 */
	protected $public_key;

	/**
	 * @var \Ebanx\Benjamin\Facade
	 */
	protected $ebanx;

	/**
	 * @var Boleto
	 */
	protected $ebanx_gateway;

	/**
	 * @var WC_Logger
	 */
	protected $log;

	/**
	 * @var string
	 */
	public $icon;

	/**
	 * @var array
	 */
	protected $names;

	/**
	 * @var string
	 */
	protected $merchant_currency;

	/**
	 * @var string
	 */
	protected $api_name;

	/**
	 * @var int
	 */
	protected static $totalGateways = 0;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		self::$totalGateways++;

		$this->user_id = get_current_user_id();
		$this->configs = new WC_EBANX_Global_Gateway();
		$this->is_sandbox_mode = ($this->configs->settings['sandbox_mode_enabled'] === 'yes');
		$this->private_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_private_key'] : $this->configs->settings['live_private_key'];
		$this->public_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_public_key'] : $this->configs->settings['live_public_key'];
		$this->ebanx = (new WC_EBANX_Api($this->configs))->ebanx();

		if ($this->configs->settings['debug_enabled'] === 'yes') {
			$this->log = new WC_Logger();
		}

		add_action('wp_enqueue_scripts', array($this, 'checkout_assets'), 100);
		add_filter('woocommerce_checkout_fields', array($this, 'checkout_fields'));

		$this->supports = array(
			'refunds',
		);

		$this->icon = $this->show_icon();
		$this->names = $this->get_billing_field_names();
		$this->merchant_currency = strtoupper(get_woocommerce_currency());
	}


	/**
	 * The main method to process the payment that came from WooCommerce checkout
	 * This method check the information sent by WooCommerce and if they are correct, sends the request to EBANX API
	 * The catch block captures the errors and checks the error code returned by EBANX API and then shows to the user the correct error message
	 *
	 * @param  int $order_id
	 * @return void
	 */
	public function process_payment($order_id)
	{
		try {
			$order = wc_get_order( $order_id );
			do_action( 'ebanx_before_process_payment', $order );

			if ( $order->get_total() > 0 ) {
				$data = WC_EBANX_Payment_Adapter::transform( $order, $this->configs, $this->api_name, $this->names );

				$response = $this->ebanx_gateway->create( $data );

				// TODO: process the payment response correctly

				$this->process_response( $response, $order );
			} else {
				$order->payment_complete();
			}
		} catch (Exception $e) {
			$country = $this->getTransactionAddress('country');

			$message = self::get_error_message($e, $country);

			WC()->session->set('refresh_totals', true);
			WC_EBANX::log("EBANX Error: $message");

			wc_add_notice($message, 'error');

			do_action('ebanx_process_payment_error', $message, $code);
			return;
		}

	}
}
