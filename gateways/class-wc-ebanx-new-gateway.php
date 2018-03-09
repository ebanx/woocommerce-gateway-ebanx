<?php

use Ebanx\Benjamin\Models\Country;

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
	public $configs;

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
	public $names;

	/**
	 * @var string
	 */
	protected $merchant_currency;

	/**
	 * @var string
	 */
	protected $api_name;

	/**
	 * @var array
	 */
	protected static $ebanx_params = [];

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
	 * Insert the necessary assets on checkout page
	 *
	 * @return void
	 */
	public function checkout_assets()
	{
		if (is_checkout()) {
			wp_enqueue_script(
				'woocommerce_ebanx_checkout_fields',
				plugins_url('assets/js/checkout-fields.js', WC_EBANX::DIR),
				array('jquery'),
				WC_EBANX::get_plugin_version(),
				true
			);
			$checkout_params = array(
				'is_sandbox' => $this->is_sandbox_mode,
				'sandbox_tag_messages' => array(
					'pt-br' => 'EM TESTE',
					'es' => 'EN PRUEBA',
				),
			);
			wp_localize_script( 'woocommerce_ebanx_checkout_fields', 'wc_ebanx_checkout_params', apply_filters( 'wc_ebanx_checkout_params', $checkout_params ) );
		}

		if ( is_checkout() && $this->is_sandbox_mode ) {
			wp_enqueue_style(
				'woocommerce_ebanx_sandbox_style',
				plugins_url( 'assets/css/sandbox-checkout-alert.css', WC_EBANX::DIR )
			);
		}

		if (
			is_wc_endpoint_url( 'order-pay' ) ||
			is_wc_endpoint_url( 'order-received' ) ||
			is_wc_endpoint_url( 'view-order' ) ||
			is_checkout()
		) {
			wp_enqueue_style(
				'woocommerce_ebanx_paying_via_ebanx_style',
				plugins_url('assets/css/paying-via-ebanx.css', WC_EBANX::DIR)
			);

			static::$ebanx_params = array(
				'key'  => $this->public_key,
				'mode' => $this->is_sandbox_mode ? 'test' : 'production',
				'ajaxurl' =>  admin_url('admin-ajax.php', null)
			);

			self::$initializedGateways++;

			if (self::$initializedGateways === self::$totalGateways) {
				wp_localize_script('woocommerce_ebanx_credit_card', 'wc_ebanx_params', apply_filters('wc_ebanx_params', static::$ebanx_params));
			}
		}
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
				$data = $this->transform_payment_data( $order );

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
	 * @param WC_Order $order
	 *
	 * @return \Ebanx\Benjamin\Models\Payment
	 * @throws Exception
	 */
	protected function transform_payment_data( $order ) {
		return WC_EBANX_Payment_Adapter::transform( $order, $this->configs, $this->api_name, $this->names );
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

		$error_message = __(sprintf('EBANX: An error occurred: %s - %s', $code, $status_message), 'woocommerce-gateway-ebanx');

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
		$country = trim( strtolower( $order->billing_country ) );

		$document = $this->save_document( $country );

		if ($document !== false) {
			update_user_meta( $this->user_id, '_ebanx_document', $document );
		}
	}

	/**
	 * @param int $order_id
	 * @param null $amount
	 * @param string $reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund($order_id, $amount = null, $reason = '')
	{
		try {
			$order = wc_get_order($order_id);

			$hash = get_post_meta($order_id, '_ebanx_payment_hash', true);

			do_action('ebanx_before_process_refund', $order, $hash);

			if (!$order || is_null($amount) || !$hash) {
				return false;
			}

			if ( empty($reason) ) {
				$reason = __('No reason specified.', 'woocommerce-gateway-ebanx');
			}

			$response = $this->ebanx->refund()->requestByHash($hash, $amount, $reason);

			if ($response['status'] !== 'SUCCESS') {
				do_action('ebanx_process_refund_error', $order, $response);

				switch ($response['status_code']) {
					case 'BP-REF-7':
						$message = __('The payment cannot be refunded because it is not confirmed.', 'woocommerce-gateway-ebanx');
						break;
					default:
						$message = $response['status_message'];
				}

				return new WP_Error('ebanx_process_refund_error', $message);
			}

			$refunds = $response['payment']['refunds'];

			$order->add_order_note(sprintf(__('EBANX: Refund requested. %s - Refund ID: %s - Reason: %s.', 'woocommerce-gateway-ebanx'), wc_price($amount), $response['refund']['id'], $reason));

			update_post_meta($order_id, '_ebanx_payment_refunds', $refunds);

			do_action('ebanx_after_process_refund', $order, $response, $refunds);

			return true;
		}
		catch (Exception $e) {
			return new WP_Error('ebanx_process_refund_error', __('We could not finish processing this refund. Please try again.'));
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
				&& is_array( $response['payment']['transaction_status'] )
				&& array_key_exists( 'code', $response['payment']['transaction_status'] )
				&& $response['payment']['transaction_status']['code'] === 'NOK';
	}

	/**
	 * @param string $country
	 *
	 * @return string
	 * @throws Exception
	 */
	private function save_document( $country ) {
		$field_name = 'document';
		if ( $country === WC_EBANX_Constants::COUNTRY_BRAZIL ) {
			if ( WC_EBANX_Request::has( $this->names['ebanx_billing_brazil_person_type'] ) ) {
				update_user_meta( $this->user_id, '_ebanx_billing_brazil_person_type', sanitize_text_field( WC_EBANX_Request::read( $this->names['ebanx_billing_brazil_person_type'] ) ) );
			}

			if (WC_EBANX_Request::has( $this->names['ebanx_billing_brazil_cnpj'])) {
				$field_name =  'cnpj';
			}
		}

		$country = strtolower(Country::fromIso(strtoupper($country)));
		$document = sanitize_text_field( WC_EBANX_Request::read( $this->names['ebanx_billing_' . $country . '_' . $field_name] ) );

		update_user_meta( $this->user_id, '_ebanx_billing_' . $country . '_' . $field_name, $document );

		return $document;
	}
}
