<?php

use Ebanx\Benjamin\Models\Country;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Update converted value via ajax.
add_action( 'wp_ajax_nopriv_ebanx_update_converted_value', 'ebanx_update_converted_value' );
add_action( 'wp_ajax_ebanx_update_converted_value', 'ebanx_update_converted_value' );

/**
 * It's a just a method to call `ebanx_update_converted_value`
 * to avoid WordPress hooks problem
 *
 * @throws Exception Throws missing parameter exception.
 * @return void
 */
function ebanx_update_converted_value() {
	echo WC_EBANX_Exchange_Rate::checkout_rate_conversion( // phpcs:ignore WordPress.XSS.EscapeOutput
		WC_EBANX_Request::read( 'currency' ),
		false,
		WC_EBANX_Request::read( 'country' ), // phpcs:ignore WordPress.XSS.EscapeOutput
		WC_EBANX_Request::read( 'instalments' ) // phpcs:ignore WordPress.XSS.EscapeOutput
	);

	wp_die();
}

/**
 * Class WC_EBANX_New_Gateway
 */
class WC_EBANX_Gateway extends WC_Payment_Gateway {
	/**
	 *
	 * @var int
	 */
	public $user_id;

	/**
	 *
	 * @var WC_EBANX_Global_Gateway
	 */
	public $configs;

	/**
	 *
	 * @var bool
	 */
	protected $is_sandbox_mode;

	/**
	 *
	 * @var string
	 */
	protected $private_key;

	/**
	 *
	 * @var string
	 */
	protected $public_key;

	/**
	 *
	 * @var \Ebanx\Benjamin\Facade
	 */
	protected $ebanx;

	/**
	 *
	 * @var \Ebanx\Benjamin\Services\Gateways\DirectGateway
	 */
	protected $ebanx_gateway;

	/**
	 *
	 * @var WC_Logger
	 */
	protected $log;

	/**
	 *
	 * @var string
	 */
	public $icon;

	/**
	 *
	 * @var array
	 */
	public $names;

	/**
	 *
	 * @var string
	 */
	protected $merchant_currency;

	/**
	 *
	 * @var string
	 */
	protected $api_name;

	/**
	 *
	 * @var array
	 */
	protected static $ebanx_params = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->user_id         = get_current_user_id();
		$this->configs         = new WC_EBANX_Global_Gateway();
		$this->is_sandbox_mode = ( 'yes' === $this->configs->settings['sandbox_mode_enabled'] );
		$this->private_key     = $this->is_sandbox_mode ? $this->configs->settings['sandbox_private_key'] : $this->configs->settings['live_private_key'];
		$this->public_key      = $this->is_sandbox_mode ? $this->configs->settings['sandbox_public_key'] : $this->configs->settings['live_public_key'];
		$this->ebanx           = ( new WC_EBANX_Api( $this->configs ) )->ebanx();

		if ( 'yes' === $this->configs->settings['debug_enabled'] ) {
			$this->log = new WC_Logger();
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'checkout_assets' ], 100 );
		add_filter( 'woocommerce_checkout_fields', [ $this, 'checkout_fields' ] );

		$this->supports = [
			'refunds',
		];

		$this->icon              = plugins_url( '/assets/images/' . $this->id . '.png', plugin_basename( dirname( __FILE__ ) ) );
		$this->names             = $this->get_billing_field_names();
		$this->merchant_currency = strtoupper( get_woocommerce_currency() );
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 */
	public function is_available() {
		return parent::is_available()
			&& 'yes' === $this->enabled
			&& ! empty( $this->public_key )
			&& ! empty( $this->private_key );
	}

	/**
	 * Output the admin settings in the correct format.
	 *
	 * @return void
	 */
	public function admin_options() {
		include WC_EBANX_TEMPLATES_DIR . 'views/html-admin-page.php';
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param  array $data
	 * @return void
	 */
	public static function thankyou_page( $data ) {
		$file_name = "{$data['method']}/payment-{$data['order_status']}.php";

		if ( file_exists( WC_EBANX::get_templates_path() . $file_name ) ) {
			wc_get_template(
				$file_name,
				$data['data'],
				'woocommerce/ebanx/',
				WC_EBANX::get_templates_path()
			);
		}
	}

	/**
	 * Insert the necessary assets on checkout page
	 *
	 * @return void
	 */
	public function checkout_assets() {
		if ( is_checkout() ) {
			wp_enqueue_script(
				'woocommerce_ebanx_checkout_fields',
				plugins_url( 'assets/js/checkout-fields.js', WC_EBANX::DIR ),
				[ 'jquery' ],
				WC_EBANX::get_plugin_version(),
				true
			);
			$checkout_params = [
				'is_sandbox'           => $this->is_sandbox_mode,
				'sandbox_tag_messages' => [
					'pt-br' => 'EM TESTE',
					'es'    => 'EN PRUEBA',
				],
			];
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
				plugins_url( 'assets/css/paying-via-ebanx.css', WC_EBANX::DIR )
			);

			static::$ebanx_params = [
				'key'     => $this->public_key,
				'mode'    => $this->is_sandbox_mode ? 'test' : 'production',
				'ajaxurl' => admin_url( 'admin-ajax.php', null ),
			];

			wp_localize_script( 'woocommerce_ebanx_credit_card', 'wc_ebanx_params', apply_filters( 'wc_ebanx_params', static::$ebanx_params ) );
		}
	}

	/**
	 * Fetches the billing field names for compatibility with checkout managers
	 *
	 * @return array
	 */
	public function get_billing_field_names() {
		return array(
			// Brazil General.
			'ebanx_billing_brazil_person_type'      => $this->get_checkout_manager_settings_or_default( 'checkout_manager_brazil_person_type', 'ebanx_billing_brazil_person_type' ),

			// Brazil CPF.
			'ebanx_billing_brazil_document'         => $this->get_checkout_manager_settings_or_default( 'checkout_manager_cpf_brazil', 'ebanx_billing_brazil_document' ),

			// Brazil CNPJ.
			'ebanx_billing_brazil_cnpj'             => $this->get_checkout_manager_settings_or_default( 'checkout_manager_cnpj_brazil', 'ebanx_billing_brazil_cnpj' ),

			// Chile Fields.
			'ebanx_billing_chile_document'          => $this->get_checkout_manager_settings_or_default( 'checkout_manager_chile_document', 'ebanx_billing_chile_document' ),

			// Colombia Fields.
			'ebanx_billing_colombia_document'       => $this->get_checkout_manager_settings_or_default( 'checkout_manager_colombia_document', 'ebanx_billing_colombia_document' ),

			// Argentina Fields.
			'ebanx_billing_argentina_document_type' => $this->get_checkout_manager_settings_or_default( 'checkout_manager_argentina_document_type', 'ebanx_billing_argentina_document_type' ),
			'ebanx_billing_argentina_document'      => $this->get_checkout_manager_settings_or_default( 'checkout_manager_argentina_document', 'ebanx_billing_argentina_document' ),

			// Peru Fields.
			'ebanx_billing_peru_document'           => $this->get_checkout_manager_settings_or_default( 'checkout_manager_peru_document', 'ebanx_billing_peru_document' ),
		);
	}

	/**
	 * Fetches a single checkout manager setting from the gateway settings if found, otherwise it returns an optional default value
	 *
	 * @param  string $name    The setting name to fetch.
	 * @param  string $default The default value in case setting is not present.
	 *
	 * @return string
	 */
	private function get_checkout_manager_settings_or_default( $name, $default = null ) {
		if ( ! isset( $this->configs->settings['checkout_manager_enabled'] ) || 'yes' !== $this->configs->settings['checkout_manager_enabled'] ) {
			return $default;
		}

		return $this->configs->get_setting_or_default( $name, $default );
	}

	/**
	 * Save order's meta fields for future use
	 *
	 * @param  WC_Order $order The order created.
	 * @param  Object   $request The request from EBANX success response.
	 *
	 * @return void
	 * @throws Exception Param missing.
	 */
	protected function save_order_meta_fields( $order, $request ) {
		update_post_meta( $order->id, '_ebanx_payment_hash', $request->payment->hash );
		update_post_meta( $order->id, '_ebanx_payment_open_date', $request->payment->open_date );

		if ( WC_EBANX_Request::has( 'billing_email' ) ) {
			update_post_meta( $order->id, '_ebanx_payment_customer_email', sanitize_email( WC_EBANX_Request::read( 'billing_email' ) ) );
		}

		if ( WC_EBANX_Request::has( 'billing_phone' ) ) {
			update_post_meta( $order->id, '_ebanx_payment_customer_phone', sanitize_text_field( WC_EBANX_Request::read( 'billing_phone' ) ) );
		}

		if ( WC_EBANX_Request::has( 'billing_address_1' ) ) {
			update_post_meta( $order->id, '_ebanx_payment_customer_address', sanitize_text_field( WC_EBANX_Request::read( 'billing_address_1' ) ) );
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
	public function process_payment( $order_id ) {
		try {
			$order = wc_get_order( $order_id );
			apply_filters( 'ebanx_before_process_payment', $order );

			if ( $order->get_total() > 0 ) {
				$data = $this->transform_payment_data( $order );

				$response = $this->ebanx_gateway->create( $data );

				WC_EBANX_Checkout_Logger::persist(
					[
						'request'  => $data,
						'response' => $response,
					]
				);

				$this->process_response( $response, $order );
			} else {
				$order->payment_complete();
			}

			do_action( 'ebanx_after_process_payment', $order );

			return $this->dispatch(
				[
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				]
			);
		} catch ( Exception $e ) {
			$country = $this->get_transaction_address( 'country' );

			$message = WC_EBANX_Errors::get_error_message( $e, $country );

			WC()->session->set( 'refresh_totals', true );
			WC_EBANX::log( "EBANX Error: $message" );

			wc_add_notice( $message, 'error' );

			do_action( 'ebanx_process_payment_error', $message );
			return [];
		}
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return \Ebanx\Benjamin\Models\Payment
	 * @throws Exception Throws parameter missing exception.
	 */
	protected function transform_payment_data( $order ) {
		return WC_EBANX_Payment_Adapter::transform( $order, $this->configs, $this->names );
	}

	/**
	 * Clean the cart and dispatch the data to request
	 *
	 * @param  array $data  The checkout's data.
	 *
	 * @return array
	 */
	protected function dispatch( $data ) {
		WC()->cart->empty_cart();

		return $data;
	}

	/**
	 * Get the customer's address
	 *
	 * @param  string $attr
	 *
	 * @return boolean|string
	 * @throws Exception Throws parameter missing message.
	 */
	public function get_transaction_address( $attr = '' ) {
		if (
			! isset( WC()->customer )
			|| is_admin()
			|| empty( WC_EBANX_Request::read( 'billing_country', null ) )
			&& empty( WC()->customer->get_country() )
		) {
			return false;
		}

		$address['country'] = trim( strtolower( WC()->customer->get_country() ) );
		if ( ! empty( WC_EBANX_Request::read( 'billing_country', null ) ) ) {
			$address['country'] = trim( strtolower( WC_EBANX_Request::read( 'billing_country' ) ) );
		}

		if ( '' !== $attr && ! empty( $address[ $attr ] ) ) {
			return $address[ $attr ];
		}
		return $address;
	}

	/**
	 *
	 * @param array    $response
	 * @param WC_Order $order
	 *
	 * @throws WC_EBANX_Payment_Exception Throws error message.
	 * @throws Exception Throws parameter missing exception.
	 */
	protected function process_response( $response, $order ) {
		// translators: placeholder contains request response.
		WC_EBANX::log( sprintf( __( 'Processing response: %s', 'woocommerce-gateway-ebanx' ), print_r( $response, true ) ) );

		if ( 'SUCCESS' !== $response['status'] ) {
			$this->process_response_error( $response, $order );
		}

		// translators: placeholder contains ebanx payment hash.
		$message = sprintf( __( 'Payment approved. Hash: %s', 'woocommerce-gateway-ebanx' ), $response['payment']['hash'] );

		WC_EBANX::log( $message );

		$payment_status = $response['payment']['status'];
		if ( $response['payment']['pre_approved'] && 'CO' === $payment_status ) {
			$order->payment_complete( $response['payment']['hash'] );
		}

		$order->add_order_note( $this->get_order_note_from_payment_status( $payment_status ) );
		$order->update_status( $this->get_order_status_from_payment_status( $payment_status ) );

		// Save post's meta fields.
		$this->save_order_meta_fields( $order, WC_EBANX_Helper::array_to_object( $response ) );

		// Save user's fields.
		$this->save_user_meta_fields( $order );

		do_action( 'ebanx_process_response', $order );
	}

	/**
	 *
	 * @param array    $response
	 * @param WC_Order $order
	 *
	 * @throws WC_EBANX_Payment_Exception Throws error message.
	 * @throws Exception Throws parameter missing exception.
	 */
	protected function process_response_error( $response, $order ) {
		if (
			isset( $response['payment']['transaction_status'] )
			&& 'NOK' === $response['payment']['transaction_status']['code']
			&& 'EBANX' === $response['payment']['transaction_status']['acquirer']
			&& $this->is_sandbox_mode
		) {
			throw new Exception( 'SANDBOX-INVALID-CC-NUMBER' );
		}

		$code           = array_key_exists( 'status_code', $response ) ? $response['status_code'] : 'GENERAL';
		$status_message = array_key_exists( 'status_message', $response ) ? $response['status_message'] : '';

		if ( $this->is_refused_credit_card( $response, $code ) ) {
			$code           = 'REFUSED-CC';
			$status_message = $response['payment']['transaction_status']['description'];
		}

		// translators: placeholders contain bp-dr code and corresponding message.
		$error_message = sprintf( __( 'EBANX: An error occurred: %1$s - %2$s', 'woocommerce-gateway-ebanx' ), $code, $status_message );

		$order->update_status( 'failed', $error_message );
		$order->add_order_note( $error_message );

		do_action( 'ebanx_process_response_error', $order, $code );

		throw new WC_EBANX_Payment_Exception( $status_message, $code );
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @throws Exception Throws parameter missing exception.
	 */
	protected function save_user_meta_fields( $order ) {
		if ( ! $this->user_id ) {
			$this->user_id = get_current_user_id();
		}

		if ( ! isset( $this->user_id ) ) {
			return;
		}
		$country = trim( strtolower( $order->billing_country ) );

		$document = $this->save_document( $country );

		if ( false !== $document ) {
			update_user_meta( $this->user_id, '_ebanx_document', $document );
		}
	}

	/**
	 *
	 * @param int    $order_id
	 * @param null   $amount
	 * @param string $reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		try {
			$order = wc_get_order( $order_id );

			$hash = get_post_meta( $order_id, '_ebanx_payment_hash', true );

			do_action( 'ebanx_before_process_refund', $order, $hash );

			if ( ! $order || is_null( $amount ) || ! $hash ) {
				return false;
			}

			if ( empty( $reason ) ) {
				$reason = __( 'No reason specified.', 'woocommerce-gateway-ebanx' );
			}

			$response = $this->ebanx->refund()->requestByHash( $hash, $amount, $reason );

			WC_EBANX_Refund_Logger::persist(
				[
					'request'  => [ $hash, $amount, $reason ],
					'response' => $response, // Response from request to EBANX.
				]
			);

			if ( 'SUCCESS' !== $response['status'] ) {
				do_action( 'ebanx_process_refund_error', $order, $response );

				switch ( $response['status_code'] ) {
					case 'BP-REF-7':
						$message = __( 'The payment cannot be refunded because it is not confirmed.', 'woocommerce-gateway-ebanx' );
						break;
					default:
						$message = $response['status_message'];
				}

				return new WP_Error( 'ebanx_process_refund_error', $message );
			}

			$refunds = $response['payment']['refunds'];

			// translators: plasceholders contain amount, refund id and reason.
			$order->add_order_note( sprintf( __( 'EBANX: Refund requested. %1$s - Refund ID: %2$s - Reason: %3$s.', 'woocommerce-gateway-ebanx' ), wc_price( $amount ), $response['refund']['id'], $reason ) );

			update_post_meta( $order_id, '_ebanx_payment_refunds', $refunds );

			do_action( 'ebanx_after_process_refund', $order, $response, $refunds );

			return true;
		} catch ( Exception $e ) {
			return new WP_Error( 'ebanx_process_refund_error', __( 'We could not finish processing this refund. Please try again.', 'woocommerce-gateway-ebanx' ) );
		}
	}

	/**
	 * Create the hooks to process cash payments
	 *
	 * @param  array  $codes
	 * @param  string $notification_type
	 *
	 * @return WC_Order
	 */
	final public function process_hook( array $codes, $notification_type ) {
		do_action( 'ebanx_before_process_hook', $codes, $notification_type );

		WC_EBANX_Notification_Received_Logger::persist( [ 'data' => $_GET ] );

		if ( isset( $codes['hash'] ) && ! empty( $codes['hash'] ) && isset( $codes['merchant_payment_code'] ) && ! empty( $codes['merchant_payment_code'] ) ) {
			unset( $codes['merchant_payment_code'] );
		}

		$data = $this->ebanx->paymentInfo()->findByHash( $codes['hash'], $this->is_sandbox_mode );

		WC_EBANX_Notification_Query_Logger::persist(
			[
				'codes' => $codes,
				'data'  => $data,
			]
		);

		$order_id = WC_EBANX_Helper::get_post_id_by_meta_key_and_value( '_ebanx_payment_hash', $data['payment']['hash'] );

		$order = new WC_Order( $order_id );

		switch ( strtoupper( $notification_type ) ) {
			case 'REFUND':
				$this->process_refund_hook( $order, $data );

				break;
			case 'UPDATE':
				$this->update_payment( $order, $data );

				break;
		};

		do_action( 'ebanx_after_process_hook', $order, $notification_type );

		return $order;
	}

	/**
	 * Updates the payment when receive a notification from EBANX
	 *
	 * @param WC_Order $order
	 * @param array    $data
	 * @return void
	 */
	final public function update_payment( $order, $data ) {
		$request_status = strtoupper( $data['payment']['status'] );

		if ( 'completed' === $order->status && 'CA' !== $request_status ) {
			return;
		}

		$status     = [
			'CO' => 'Confirmed',
			'CA' => 'Canceled',
			'PE' => 'Pending',
			'OP' => 'Opened',
		];
		$new_status = null;
		$old_status = $order->status;

		switch ( $request_status ) {
			case 'CO':
				if ( method_exists( $order, 'get_payment_method' )
					&& strpos( $order->get_payment_method(), 'ebanx-credit-card' ) === 0 ) {
					return;
				}
				$order->payment_complete( $data['payment']['hash'] );
				$new_status = 'processing';
				break;
			case 'CA':
				$order->payment_complete();
				$new_status = 'failed';
				break;
			case 'PE':
				$new_status = 'on-hold';
				break;
			case 'OP':
				$new_status = 'pending';
				break;
		}

		if ( $new_status !== $old_status ) {
			$payment_status = $status[ $data['payment']['status'] ];
			$order->add_order_note( sprintf( __( 'EBANX: The payment has been updated to: %s.', 'woocommerce-gateway-ebanx' ), $payment_status ) );
			$order->update_status( $new_status );
		}
	}

	/**
	 * Updates the refunds when receivers a EBANX refund notification
	 *
	 * @param WC_Order $order
	 * @param array    $data
	 * @return void
	 */
	final public function process_refund_hook( $order, $data ) {
		$refunds = current( get_post_meta( $order->id, '_ebanx_payment_refunds' ) );

		foreach ( $refunds as $k => $ref ) {
			foreach ( $data['payment']['refunds'] as $refund ) {
				if ( $ref['id'] === $refund['id'] ) {
					if ( 'CO' === $refund['status'] && 'CO' !== $refunds[ $k ]['status'] ) {
						// translators: placeholder contains refund id.
						$order->add_order_note( sprintf( __( 'EBANX: Your Refund was confirmed to EBANX - Refund ID: %s', 'woocommerce-gateway-ebanx' ), $refund['id'] ) );
					}
					if ( 'CA' === $refund['status'] && 'CA' !== $refunds[ $k ]['status'] ) {
						// translators: placeholder contains refund id.
						$order->add_order_note( sprintf( __( 'EBANX: Your Refund was canceled to EBANX - Refund ID: %s', 'woocommerce-gateway-ebanx' ), $refund['id'] ) );
					}

					$refunds[ $k ]['status']       = $refund['status'];
					$refunds[ $k ]['cancel_date']  = $refund['cancel_date'];
					$refunds[ $k ]['request_date'] = $refund['request_date'];
					$refunds[ $k ]['pending_date'] = $refund['pending_date'];
					$refunds[ $k ]['confirm_date'] = $refund['confirm_date'];
				}
			}
		}

		update_post_meta( $order->id, '_ebanx_payment_refunds', $refunds );
	}

	/**
	 *
	 * @param string $status
	 *
	 * @return string
	 */
	private function get_order_note_from_payment_status( $status ) {
		$notes = [
			'CO' => __( 'EBANX: The transaction was paid.', 'woocommerce-gateway-ebanx' ),
			'PE' => __( 'EBANX: The order is awaiting payment.', 'woocommerce-gateway-ebanx' ),
			'OP' => __( 'EBANX: The payment was opened.', 'woocommerce-gateway-ebanx' ),
			'CA' => __( 'EBANX: The payment has failed.', 'woocommerce-gateway-ebanx' ),
		];

		return $notes[ strtoupper( $status ) ];
	}

	/**
	 *
	 * @param string $payment_status
	 *
	 * @return string
	 */
	private function get_order_status_from_payment_status( $payment_status ) {
		$order_status = [
			'CO' => 'processing',
			'PE' => 'on-hold',
			'CA' => 'failed',
			'OP' => 'pending',
		];

		return $order_status[ strtoupper( $payment_status ) ];
	}

	/**
	 *
	 * @param array  $response
	 * @param string $code
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
				&& 'NOK' === $response['payment']['transaction_status']['code'];
	}

	/**
	 *
	 * @param string $country
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private function save_document( $country ) {
		$field_name = 'document';
		if ( WC_EBANX_Constants::COUNTRY_BRAZIL === $country ) {
			if ( WC_EBANX_Request::has( $this->names['ebanx_billing_brazil_person_type'] ) ) {
				update_user_meta( $this->user_id, '_ebanx_billing_brazil_person_type', sanitize_text_field( WC_EBANX_Request::read( $this->names['ebanx_billing_brazil_person_type'] ) ) );
			}

			if ( WC_EBANX_Request::has( $this->names['ebanx_billing_brazil_cnpj'] ) ) {
				$field_name = 'cnpj';
			}
		}

		$country = strtolower( Country::fromIso( strtoupper( $country ) ) );

		if ( ! WC_EBANX_Request::has( $this->names[ 'ebanx_billing_' . $country . '_' . $field_name ] ) ) {
			return false;
		}

		$document = sanitize_text_field( WC_EBANX_Request::read( $this->names[ 'ebanx_billing_' . $country . '_' . $field_name ] ) );

		update_user_meta( $this->user_id, '_ebanx_billing_' . $country . '_' . $field_name, $document );

		return $document;
	}

	/**
	 * Insert custom billing fields on checkout page
	 *
	 * @param  array $fields WooCommerce's fields.
	 * @return array         The new fields.
	 */
	public function checkout_fields( $fields ) {
		$required_mark = ' <abbr class="required" title="required">*</abbr>';

		$fields_options = array();
		if ( isset( $this->configs->settings['brazil_taxes_options'] ) && is_array( $this->configs->settings['brazil_taxes_options'] ) ) {
			$fields_options = $this->configs->settings['brazil_taxes_options'];
		}

		$disable_own_fields = isset( $this->configs->settings['checkout_manager_enabled'] ) && 'yes' === $this->configs->settings['checkout_manager_enabled'];

		$cpf = get_user_meta( $this->user_id, '_ebanx_billing_brazil_document', true );

		$cnpj = get_user_meta( $this->user_id, '_ebanx_billing_brazil_cnpj', true );

		$rut = get_user_meta( $this->user_id, '_ebanx_billing_chile_document', true );

		$dni = get_user_meta( $this->user_id, '_ebanx_billing_colombia_document', true );

		$dni_pe = get_user_meta( $this->user_id, '_ebanx_billing_peru_document', true );

		$cdi = get_user_meta( $this->user_id, '_ebanx_billing_argentina_document', true );

		$ebanx_billing_brazil_person_type = array(
			'type'    => 'select',
			'label'   => __( 'Select an option', 'woocommerce-gateway-ebanx' ),
			'default' => 'cpf',
			'class'   => array( 'ebanx_billing_brazil_selector', 'ebanx-select-field' ),
			'options' => array(
				'cpf'  => __( 'CPF - Individuals', 'woocommerce-gateway-ebanx' ),
				'cnpj' => __( 'CNPJ - Companies', 'woocommerce-gateway-ebanx' ),
			),
		);

		$ebanx_billing_argentina_document_type = array(
			'type'    => 'select',
			'label'   => __( 'Select a document type', 'woocommerce-gateway-ebanx' ),
			'default' => 'ARG_CUIT',
			'class'   => array( 'ebanx_billing_argentina_selector', 'ebanx-select-field' ),
			'options' => array(
				'ARG_CUIT' => __( 'CUIT', 'woocommerce-gateway-ebanx' ),
				'ARG_CUIL' => __( 'CUIL', 'woocommerce-gateway-ebanx' ),
				'ARG_CDI'  => __( 'CDI', 'woocommerce-gateway-ebanx' ),
			),
		);

		$ebanx_billing_brazil_document = array(
			'type'    => 'text',
			'label'   => 'CPF' . $required_mark,
			'class'   => array( 'ebanx_billing_brazil_document', 'ebanx_billing_brazil_cpf', 'ebanx_billing_brazil_selector_option', 'form-row-wide' ),
			'default' => isset( $cpf ) ? $cpf : '',
		);

		$ebanx_billing_brazil_cnpj = array(
			'type'    => 'text',
			'label'   => 'CNPJ' . $required_mark,
			'class'   => array( 'ebanx_billing_brazil_cnpj', 'ebanx_billing_brazil_cnpj', 'ebanx_billing_brazil_selector_option', 'form-row-wide' ),
			'default' => isset( $cnpj ) ? $cnpj : '',
		);

		$ebanx_billing_chile_document     = array(
			'type'    => 'text',
			'label'   => 'RUT' . $required_mark,
			'class'   => array( 'ebanx_billing_chile_document', 'form-row-wide' ),
			'default' => isset( $rut ) ? $rut : '',
		);
		$ebanx_billing_colombia_document  = array(
			'type'    => 'text',
			'label'   => 'DNI' . $required_mark,
			'class'   => array( 'ebanx_billing_colombia_document', 'form-row-wide' ),
			'default' => isset( $dni ) ? $dni : '',
		);
		$ebanx_billing_peru_document      = array(
			'type'    => 'text',
			'label'   => 'DNI' . $required_mark,
			'class'   => array( 'ebanx_billing_peru_document', 'form-row-wide' ),
			'default' => isset( $dni_pe ) ? $dni_pe : '',
		);
		$ebanx_billing_argentina_document = array(
			'type'    => 'text',
			'label'   => __( 'Document', 'woocommerce-gateway-ebanx' ) . $required_mark,
			'class'   => array( 'ebanx_billing_argentina_document', 'form-row-wide' ),
			'default' => isset( $cdi ) ? $cdi : '',
		);

		if ( ! $disable_own_fields ) {
			// CPF and CNPJ are enabled.
			if ( in_array( 'cpf', $fields_options ) && in_array( 'cnpj', $fields_options ) ) {
				$fields['billing']['ebanx_billing_brazil_person_type'] = $ebanx_billing_brazil_person_type;
			}

			// CPF is enabled.
			if ( in_array( 'cpf', $fields_options ) ) {
				$fields['billing']['ebanx_billing_brazil_document'] = $ebanx_billing_brazil_document;
			}

			// CNPJ is enabled.
			if ( in_array( 'cnpj', $fields_options ) ) {
				$fields['billing']['ebanx_billing_brazil_cnpj'] = $ebanx_billing_brazil_cnpj;
			}

			// For Chile.
			$fields['billing']['ebanx_billing_chile_document'] = $ebanx_billing_chile_document;

			// For Colombia.
			$fields['billing']['ebanx_billing_colombia_document'] = $ebanx_billing_colombia_document;

			// For Argentina.
			$fields['billing']['ebanx_billing_argentina_document_type'] = $ebanx_billing_argentina_document_type;
			$fields['billing']['ebanx_billing_argentina_document']      = $ebanx_billing_argentina_document;

			// For Peru.
			$fields['billing']['ebanx_billing_peru_document'] = $ebanx_billing_peru_document;

		}

		return $fields;
	}
}
