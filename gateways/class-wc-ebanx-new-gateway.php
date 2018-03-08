<?php

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
	 * @var \Ebanx\Benjamin\Services\Gateways\DirectGateway
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
	 * This method checks the information sent by WooCommerce and if they are correct, sends a request to EBANX API
	 * The catch block captures the errors and checks the error code returned by EBANX API and then shows to the user the correct error message
	 *
	 * @param  int $order_id
	 * @return array
	 */
	public function process_payment($order_id)
	{
		try {
			$order = wc_get_order( $order_id );
			do_action( 'ebanx_before_process_payment', $order );

			if ( $order->get_total() > 0 ) {
				$data = WC_EBANX_Payment_Adapter::transform( $order, $this->configs, $this->api_name, $this->names );

				$response = $this->ebanx_gateway->create( $data );

				$this->process_response( $response, $order );
			} else {
				$order->payment_complete();
			}

			do_action('ebanx_after_process_payment', $order);

			return $this->dispatch(array(
				'result'   => 'success',
				'redirect' => $this->get_return_url($order),
			));
		} catch (Exception $e) {
			$country = $this->getTransactionAddress('country');

			$message = WC_EBANX_Errors::get_error_message($e, $country);

			WC()->session->set('refresh_totals', true);
			WC_EBANX::log("EBANX Error: $message");

			wc_add_notice($message, 'error');

			do_action( 'ebanx_process_payment_error', $message );
			return [];
		}
	}

	/**
	 * @param array $response
	 * @param WC_Order $order
	 *
	 * @throws WC_EBANX_Payment_Exception
	 * @throws Exception
	 */
	protected function process_response($response, $order)
	{
		WC_EBANX::log(sprintf(__('Processing response: %s', 'woocommerce-gateway-ebanx'), print_r($response, true)));

		if ( $response['status'] !== 'SUCCESS') {
			$this->process_response_error($response, $order);
		}

		$message = __(sprintf('Payment approved. Hash: %s', $response['payment']['hash']), 'woocommerce-gateway-ebanx');

		WC_EBANX::log($message);

		$payment_status = $response['payment']['status'];
		if ( $response['payment']['pre_approved'] && $payment_status == 'CO') {
			$order->payment_complete($response['payment']['hash']);
		}

		$order->add_order_note($this->get_order_note_from_payment_status($payment_status));
		$order->update_status($this->get_order_status_from_payment_status($payment_status));

		// Save post's meta fields
		$this->save_order_meta_fields( $order, WC_EBANX_Helper::arrayToObject( $response ) );

		// Save user's fields
		$this->save_user_meta_fields($order);

		do_action('ebanx_process_response', $order);
	}

	/**
	 * @param array $response
	 * @param WC_Order $order
	 *
	 * @throws WC_EBANX_Payment_Exception
	 * @throws Exception
	 */
	protected function process_response_error($response, $order)
	{
		if (
			isset($response['payment']['transaction_status'])
			&& $response['payment']['transaction_status']['code'] === 'NOK'
			&& $response['payment']['transaction_status']['acquirer'] === 'EBANX'
			&& $this->is_sandbox_mode
		) {
			throw new Exception('SANDBOX-INVALID-CC-NUMBER');
		}

		$code = array_key_exists('status_code', $response) ? $response['status_code'] : 'GENERAL';
		$status_message = array_key_exists('status_message', $response) ? $response['status_message'] : '';

		if ( $this->is_refused_credit_card( $response, $code ) ) {
			$code = 'REFUSED-CC';
			$status_message = $response['payment']['transaction_status']['description'];
		}

		$error_message = __(sprintf('EBANX: An error occurred: %s - %s', $code, $response['status_message']), 'woocommerce-gateway-ebanx');

		$order->update_status('failed', $error_message);
		$order->add_order_note($error_message);

		do_action('ebanx_process_response_error', $order, $code);

		throw new WC_EBANX_Payment_Exception($status_message, $code);
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	protected function save_user_meta_fields($order)
	{
		if ( ! $this->user_id ) {
			$this->user_id = get_current_user_id();
		}

		if ( ! isset( $this->user_id ) ) {
			return;
		}
		$document = false;

		if (trim(strtolower($order->billing_country)) === WC_EBANX_Constants::COUNTRY_BRAZIL) {
			$document = $this->use_brazilian_document();
		}

		if ($document !== false) {
			update_user_meta( $this->user_id, '_ebanx_document', $document );
		}
	}

	/**
	 * @param $status string
	 *
	 * @return string
	 */
	private function get_order_note_from_payment_status( $status )
	{
		$notes = [
			'CO' => __( 'EBANX: The transaction was paid.', 'woocommerce-gateway-ebanx' ),
			'PE' => __( 'EBANX: The order is awaiting payment.', 'woocommerce-gateway-ebanx' ),
			'OP' => __( 'EBANX: The payment was opened.', 'woocommerce-gateway-ebanx' ),
			'CA' => __( 'EBANX: The payment has failed.', 'woocommerce-gateway-ebanx' ),
		];

		return $notes[ strtoupper( $status ) ];
	}

	/**
	 * @param $payment_status string
	 *
	 * @return string
	 */
	private function get_order_status_from_payment_status( $payment_status )
	{
		$order_status = [
			'CO' => 'processing',
			'PE' => 'on-hold',
			'CA' => 'failed',
			'OP' => 'pending',
		];

		return $order_status[ strtoupper( $payment_status ) ];
	}

	/**
	 * @param $response
	 * @param $code
	 *
	 * @return bool
	 */
	private function is_refused_credit_card( $response, $code ) {
		return 'GENERAL' === $code
				&& array_key_exists( 'payment', $response )
				&& is_array( $response['payment'] )
				&& array_key_exists( 'transaction_status', $response['payment'] )
				&& isset( $response['payment']['transaction_status'] )
				&& $response->payment->transaction_status->code === 'NOK';
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	private function use_brazilian_document() {
		if ( WC_EBANX_Request::has( $this->names['ebanx_billing_brazil_person_type'] ) ) {
			update_user_meta( $this->user_id, '_ebanx_billing_brazil_person_type', sanitize_text_field( WC_EBANX_Request::read( $this->names['ebanx_billing_brazil_person_type'] ) ) );
		}

		$field_name = WC_EBANX_Request::has( $this->names['ebanx_billing_brazil_cnpj'] ) ? 'cnpj' : 'document';
		$document = sanitize_text_field( WC_EBANX_Request::read( $this->names["ebanx_billing_brazil_$field_name"] ) );

		update_user_meta( $this->user_id, "_ebanx_billing_brazil_$field_name", $document );

		return $document;
	}
}
