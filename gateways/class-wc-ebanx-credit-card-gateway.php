<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Credit_Card_Gateway
 */
abstract class WC_EBANX_Credit_Card_Gateway extends WC_EBANX_Gateway {

	/**
	 * The rates for each instalment
	 *
	 * @var array
	 */
	protected $instalment_rates = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->api_name = '_creditcard';

		parent::__construct();

		$this->ebanx_gateway = $this->ebanx->creditCard();

		add_action( 'woocommerce_order_edit_status', array( $this, 'capture_payment_action' ), 10, 2 );

		if ( $this->configs->get_setting_or_default( 'interest_rates_enabled', 'no' ) == 'yes' ) {
			$max_instalments = $this->configs->settings['credit_card_instalments'];
			for ( $i = 1; $i <= $max_instalments; $i++ ) {
				$field                        = 'interest_rates_' . sprintf( '%02d', $i );
				$this->instalment_rates[ $i ] = 0;
				if ( is_numeric( $this->configs->settings[ $field ] ) ) {
					$this->instalment_rates[ $i ] = $this->configs->settings[ $field ] / 100;
				}
			}
		}

		$this->supports = array(
			'refunds',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
		);

		add_action( 'wcs_default_retry_rules', [ $this, 'retryRules' ] );
		add_action( 'woocommerce_scheduled_subscription_payment', [ $this, 'scheduled_subscription_payment' ] );
	}

	/**
	 *
	 * @return array
	 */
	public function retryRules() {
		return array(
			array(
				'retry_after_interval'            => DAY_IN_SECONDS,
				'email_template_customer'         => 'WCS_Email_Customer_Payment_Retry',
				'email_template_admin'            => 'WCS_Email_Payment_Retry',
				'status_to_apply_to_order'        => 'pending',
				'status_to_apply_to_subscription' => 'on-hold',
			),
			array(
				'retry_after_interval'            => 2 * DAY_IN_SECONDS,
				'email_template_customer'         => 'WCS_Email_Customer_Payment_Retry',
				'email_template_admin'            => 'WCS_Email_Payment_Retry',
				'status_to_apply_to_order'        => 'pending',
				'status_to_apply_to_subscription' => 'on-hold',
			),
		);
	}

	/**
	 * Process scheduled subscription payments.
	 *
	 * @param string $subscription_id subscription ID.
	 *
	 * @return bool
	 * @throws Exception Shows missing params message.
	 */
	public function scheduled_subscription_payment( $subscription_id ) {
		global $counter;
		$counter++;

		if ( 1 < $counter ) {
			return;
		}

		$order = wcs_get_subscription( $subscription_id );

		$user_cc = get_user_meta( $order->data['customer_id'], '_ebanx_credit_card_token', true );

		if ( count( $user_cc ) ) {
			$data = $this->transform_payment_data( $order );

			$ebanx = ( new WC_EBANX_Api( $this->configs ) )->ebanx();

			$response = $ebanx->creditCard()->create( $data );

			WC_EBANX_Subscription_Renewal_Logger::persist(
				array(
					'subscription_id' => $subscription_id,
					'payment_method'  => $this->id,
					'request'         => $data,
					'response'        => $response, // Response from response to EBANX.
				)
			);

			if ( 'ERROR' == $response['status'] ) {
				$order->payment_complete();
				$order->update_status( 'failed' );
				WC_EBANX::log( $response['status_message'] );
			} elseif ( 'SUCCESS' == $response['status'] ) {
				switch ( $response['payment']['status'] ) {
					case 'CO':
						$order->payment_complete( $response['payment']['hash'] );
						WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );
						$order->add_order_note( __( 'EBANX: Transaction Received', 'woocommerce-gateway-ebanx' ) );
						break;
					case 'CA':
						$order->cancel_order();
						$order->add_order_note( __( 'EBANX: Transaction Failed', 'woocommerce-gateway-ebanx' ) );
						break;
					case 'OP':
						$order->payment_failed();
						$order->add_order_note( __( 'EBANX: Transaction Pending', 'woocommerce-gateway-ebanx' ) );
						break;
				}
				return true;
			}
		}
		WC_Subscriptions_Manager::expire_subscriptions_for_order( $order );
		return false;
	}

	/**
	 * Check the Auto Capture
	 *
	 * @param  array $actions
	 * @return array
	 */
	public function auto_capture( $actions ) {
		if ( is_array( $actions ) ) {
			$actions['custom_action'] = __( 'Capture by EBANX', 'woocommerce-gateway-ebanx' );
		}

		return $actions;
	}


	/**
	 *
	 * @param int    $order_id
	 * @param string $status
	 *
	 * @throws Exception Throws missing parameter exception.
	 */
	public function capture_payment_action( $order_id, $status ) {
		$action = WC_EBANX_Request::read( 'action', null );
		$order  = wc_get_order( $order_id );

		if ( $order->payment_method !== $this->id
			|| 'processing' !== $status
			|| 'woocommerce_mark_order_status' !== $action ) {
			return;
		}

		WC_EBANX_Capture_Payment::capture_payment( $order_id );
	}

	/**
	 * Insert the necessary assets on checkout page
	 *
	 * @return void
	 */
	public function checkout_assets() {
		if ( is_checkout() ) {
			wp_enqueue_script( 'wc-credit-card-form' );
			// Using // to avoid conflicts between http and https protocols.
			wp_enqueue_script( 'ebanx', '//js.ebanx.com/ebanx-1.5.min.js', '', null, true );
			wp_enqueue_script( 'woocommerce_ebanx_jquery_mask', plugins_url( 'assets/js/jquery-mask.js', WC_EBANX::DIR ), array( 'jquery' ), WC_EBANX::get_plugin_version(), true );
			wp_enqueue_script( 'woocommerce_ebanx_credit_card', plugins_url( 'assets/js/credit-card.js', WC_EBANX::DIR ), array( 'jquery-payment', 'ebanx' ), WC_EBANX::get_plugin_version(), true );

			// If we're on the checkout page we need to pass ebanx.js the address of the order.
			if ( is_checkout_pay_page() && isset( $_GET['order'] ) && isset( $_GET['order_id'] ) ) {
				// @codingStandardsIgnoreLine
				$order_key = urldecode( $_GET['order'] );
				$order_id  = absint( $_GET['order_id'] );
				$order     = wc_get_order( $order_id );

				if ( $order->id === $order_id && $order->order_key === $order_key ) {
					static::$ebanx_params['billing_first_name'] = $order->billing_first_name;
					static::$ebanx_params['billing_last_name']  = $order->billing_last_name;
					static::$ebanx_params['billing_address_1']  = $order->billing_address_1;
					static::$ebanx_params['billing_address_2']  = $order->billing_address_2;
					static::$ebanx_params['billing_state']      = $order->billing_state;
					static::$ebanx_params['billing_city']       = $order->billing_city;
					static::$ebanx_params['billing_postcode']   = $order->billing_postcode;
					static::$ebanx_params['billing_country']    = $order->billing_country;
				}
			}
		}

		parent::checkout_assets();
	}

	/**
	 * Mount the data to send to EBANX API
	 *
	 * @param WC_Order $order
	 * @return \Ebanx\Benjamin\Models\Payment
	 * @throws Exception When missing card params or when missing device fingerprint.
	 */
	protected function transform_payment_data( $order ) {

		if ( empty( WC_EBANX_Request::read( 'ebanx_token', null ) )
			|| empty( WC_EBANX_Request::read( 'ebanx_masked_card_number', null ) )
			|| empty( WC_EBANX_Request::read( 'ebanx_brand', null ) )
			|| empty( WC_EBANX_Request::read( 'ebanx_billing_cvv', null ) )
		) {
			throw new Exception( 'MISSING-CARD-PARAMS' );
		}

		if ( empty( WC_EBANX_Request::read( 'ebanx_is_one_click', null ) ) && empty( WC_EBANX_Request::read( 'ebanx_device_fingerprint', null ) ) ) {
			throw new Exception( 'MISSING-DEVICE-FINGERPRINT' );
		}

		return WC_EBANX_Payment_Adapter::transform_card( $order, $this->configs, $this->names );
	}


	/**
	 *
	 * @param array    $request
	 * @param WC_Order $order
	 *
	 * @throws Exception Throws missing parameter exception.
	 * @throws WC_EBANX_Payment_Exception Throws error message.
	 */
	protected function process_response( $request, $order ) {
		if ( 'SUCCESS' !== $request['status'] || ! $request['payment']['pre_approved'] ) {
			$this->process_response_error( $request, $order );
		}

		parent::process_response( $request, $order );
	}

	/**
	 * Save order's meta fields for future use
	 *
	 * @param  WC_Order $order The order created.
	 * @param  Object   $request The request from EBANX success response.
	 * @return void
	 */
	protected function save_order_meta_fields( $order, $request ) {
		parent::save_order_meta_fields( $order, $request );

		update_post_meta( $order->id, '_cards_brand_name', $request->payment->payment_type_code );
		update_post_meta( $order->id, '_instalments_number', $request->payment->instalments );
		update_post_meta( $order->id, '_masked_card_number', WC_EBANX_Request::read( 'ebanx_masked_card_number' ) );
	}

	/**
	 * Save user's meta fields for future use
	 *
	 * @param  WC_Order $order The order created.
	 * @return void
	 */
	protected function save_user_meta_fields( $order ) {
		parent::save_user_meta_fields( $order );

		if ( ! $this->user_id ) {
			$this->user_id = $order->user_id;
		}

		if ( ! $this->user_id
			|| $this->configs->get_setting_or_default( 'save_card_data', 'no' ) !== 'yes'
			|| ! WC_EBANX_Request::has( 'ebanx-save-credit-card' )
			|| WC_EBANX_Request::read( 'ebanx-save-credit-card' ) !== 'yes' ) {
			return;
		}

		$cards = get_user_meta( $this->user_id, '_ebanx_credit_card_token', true );
		$cards = ! empty( $cards ) ? $cards : [];

		$card = new \stdClass();

		$card->brand         = WC_EBANX_Request::read( 'ebanx_brand' );
		$card->token         = WC_EBANX_Request::read( 'ebanx_token' );
		$card->masked_number = WC_EBANX_Request::read( 'ebanx_masked_card_number' );

		foreach ( $cards as $cd ) {
			if ( empty( $cd ) ) {
				continue;
			}

			if ( $cd->masked_number == $card->masked_number && $cd->brand == $card->brand ) {
				$cd->token = $card->token;
				unset( $card );
			}
		}

		if ( isset( $card ) ) {
			$cards[] = $card;
		}

		update_user_meta( $this->user_id, '_ebanx_credit_card_token', $cards );
	}

	/**
	 * The main method to process the payment came from WooCommerce checkout
	 * This method check the informations sent by WooCommerce and if them are fine, it sends the request to EBANX API
	 * The catch captures the errors and check the code sent by EBANX API and then show to the users the right error message
	 *
	 * @param  integer $order_id The ID of the order created.
	 *
	 * @return array
	 * @throws Exception Shows param missing message.
	 */
	public function process_payment( $order_id ) {
		$has_instalments = ( WC_EBANX_Request::has( 'ebanx_billing_instalments' ) || WC_EBANX_Request::has( 'ebanx-credit-card-installments' ) );

		if ( $has_instalments ) {

			$total_price = get_post_meta( $order_id, '_order_total', true );
			$tax_rate    = 0;
			$instalments = WC_EBANX_Request::has( 'ebanx_billing_instalments' ) ? WC_EBANX_Request::read( 'ebanx_billing_instalments' ) : WC_EBANX_Request::read( 'ebanx-credit-card-installments' );

			if ( array_key_exists( $instalments, $this->instalment_rates ) ) {
				$tax_rate = $this->instalment_rates[ $instalments ];
			}

			$total_price += $total_price * $tax_rate;
			update_post_meta( $order_id, '_order_total', $total_price );
		}

		return parent::process_payment( $order_id );
	}

	/**
	 * Checks if the payment term is allowed based on price, country and minimal instalment value
	 *
	 * @param double $price Product price used as base.
	 * @param int    $instalment_number Number of instalments.
	 * @param string $country Costumer country.
	 * @return integer
	 */
	public function is_valid_instalment_amount( $price, $instalment_number, $country = null ) {
		if ( 1 === $instalment_number ) {
			return true;
		}

		$country       = $country ?: WC()->customer->get_country();
		$currency_code = strtolower( $this->merchant_currency );

		switch ( trim( strtolower( $country ) ) ) {
			case 'br':
				$site_to_local_rate            = WC_EBANX_Exchange_Rate::get_local_currency_rate_for_site( WC_EBANX_Constants::CURRENCY_CODE_BRL, $this->configs );
				$merchant_min_instalment_value = $this->configs->get_setting_or_default( "min_instalment_value_$currency_code", 0 ) * $site_to_local_rate;
				$min_instalment_value          = max(
					WC_EBANX_Constants::ACQUIRER_MIN_INSTALMENT_VALUE_BRL,
					$merchant_min_instalment_value
				);
				break;
			case 'mx':
				$site_to_local_rate            = WC_EBANX_Exchange_Rate::get_local_currency_rate_for_site( WC_EBANX_Constants::CURRENCY_CODE_MXN, $this->configs );
				$merchant_min_instalment_value = $this->configs->get_setting_or_default( "min_instalment_value_$currency_code", 0 ) * $site_to_local_rate;
				$min_instalment_value          = max(
					WC_EBANX_Constants::ACQUIRER_MIN_INSTALMENT_VALUE_MXN,
					$merchant_min_instalment_value
				);
				break;
			case 'co':
				$site_to_local_rate            = WC_EBANX_Exchange_Rate::get_local_currency_rate_for_site( WC_EBANX_Constants::CURRENCY_CODE_COP, $this->configs );
				$merchant_min_instalment_value = $this->configs->get_setting_or_default( "min_instalment_value_$currency_code", 0 ) * $site_to_local_rate;
				$min_instalment_value          = max(
					WC_EBANX_Constants::ACQUIRER_MIN_INSTALMENT_VALUE_COP,
					$merchant_min_instalment_value
				);
				break;
			case 'ar':
				$site_to_local_rate            = WC_EBANX_Exchange_Rate::get_local_currency_rate_for_site( WC_EBANX_Constants::CURRENCY_CODE_ARS, $this->configs );
				$merchant_min_instalment_value = $this->configs->get_setting_or_default( "min_instalment_value_$currency_code", 0 ) * $site_to_local_rate;
				$min_instalment_value          = max(
					WC_EBANX_Constants::ACQUIRER_MIN_INSTALMENT_VALUE_ARS,
					$merchant_min_instalment_value
				);
				break;
		}

		if ( isset( $site_to_local_rate ) && isset( $min_instalment_value ) ) {
			$local_value      = $price * $site_to_local_rate;
			$instalment_value = $local_value / $instalment_number;
			return $instalment_value >= $min_instalment_value;
		}

		return false;
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param  WC_Order $order The order created.
	 * @return void
	 */
	public static function thankyou_page( $order ) {
		$order_amount       = $order->get_total();
		$instalments_number = get_post_meta( $order->id, '_instalments_number', true ) ?: 1;
		$country            = trim( strtolower( get_post_meta( $order->id, '_billing_country', true ) ) );
		$currency           = $order->get_order_currency();

		if ( WC_EBANX_Constants::COUNTRY_BRAZIL === $country ) {
			$order_amount += round( ( $order_amount * WC_EBANX_Constants::BRAZIL_TAX ), 2 );
		}

		$data = array(
			'data'         => array(
				'card_brand_name'    => get_post_meta( $order->id, '_cards_brand_name', true ),
				'instalments_number' => $instalments_number,
				'instalments_amount' => wc_price( round( $order_amount / $instalments_number, 2 ), array( 'currency' => $currency ) ),
				'masked_card'        => substr( get_post_meta( $order->id, '_masked_card_number', true ), -4 ),
				'customer_email'     => $order->billing_email,
				'customer_name'      => $order->billing_first_name,
				'total'              => wc_price( $order_amount, array( 'currency' => $currency ) ),
				'hash'               => get_post_meta( $order->id, '_ebanx_payment_hash', true ),
			),
			'order_status' => $order->get_status(),
			'method'       => $order->payment_method,
		);

		parent::thankyou_page( $data );
	}

	/**
	 * Calculates the interests and values of items based on interest rates settings
	 *
	 * @param int $amount      The total of the user cart.
	 * @param int $max_instalments The max number of instalments based on settings.
	 * @param int $tax The tax applied.
	 * @return array               An array of instalment with price, amount, if it has interests and the number
	 */
	public function get_payment_terms( $amount, $max_instalments, $tax = 0 ) {
		$instalments      = array();
		$instalment_taxes = $this->instalment_rates;

		for ( $number = 1; $number <= $max_instalments; ++$number ) {
			$has_interest = false;
			$cart_total   = $amount;

			if ( isset( $instalment_taxes ) && array_key_exists( $number, $instalment_taxes ) ) {
				$cart_total += $cart_total * $instalment_taxes[ $number ];
				$cart_total += $cart_total * $tax;
				if ( $instalment_taxes[ $number ] > 0 ) {
					$has_interest = true;
				}
			}

			if ( $this->is_valid_instalment_amount( $cart_total, $number ) ) {
				$instalment_price = $cart_total / $number;
				$instalment_price = round( floatval( $instalment_price ), 2 );

				$instalments[] = array(
					'price'        => $instalment_price,
					'has_interest' => $has_interest,
					'number'       => $number,
				);
			}
		}

		try {
			$apply_filters = apply_filters( 'ebanx_get_payment_terms', $instalments );
		} catch ( Exception $e ) {
			return [];
		}

		return $apply_filters;
	}

	/**
	 * The HTML structure on checkout page
	 *
	 * @throws Exception Throws missing param message.
	 */
	public function payment_fields() {
		$cart_total = $this->get_order_total();

		$cards = array();

		$save_card = $this->configs->get_setting_or_default( 'save_card_data', 'no' ) === 'yes';

		if ( $save_card ) {
			$cards = array_filter(
				(array) get_user_meta( $this->user_id, '_ebanx_credit_card_token', true ), function ( $card ) {
					return ! empty( $card->brand ) && ! empty( $card->token ) && ! empty( $card->masked_number );
				}
			);
		}

		$country = $this->get_transaction_address( 'country' );

		$max_instalments = min(
			$this->configs->settings['credit_card_instalments'],
			WC_EBANX_Constants::$max_instalments[ $country ]
		);

		$tax = 0;
		if ( get_woocommerce_currency() === WC_EBANX_Constants::CURRENCY_CODE_BRL
			&& $this->configs->get_setting_or_default( 'add_iof_to_local_amount_enabled', 'yes' ) === 'yes' ) {
			$tax = WC_EBANX_Constants::CURRENCY_CODE_BRL;
		}

		$instalments_terms = $this->get_payment_terms( $cart_total, $max_instalments, $tax );

		$currency = WC_EBANX_Constants::$local_currencies[ $country ];

		$message = WC_EBANX_Constants::get_sandbox_form_message( $country );
		wc_get_template(
			'sandbox-checkout-alert.php',
			array(
				'is_sandbox_mode' => $this->is_sandbox_mode,
				'message'         => $message,
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		wc_get_template(
			$this->id . '/payment-form.php',
			array(
				'currency'            => $currency,
				'country'             => $country,
				'instalments_terms'   => $instalments_terms,
				'currency_code'       => $this->currency_code,
				'currency_rate'       => round( floatval( WC_EBANX_Exchange_Rate::get_local_currency_rate_for_site( $this->currency_code, $this->configs ) ), 2 ),
				'cards'               => (array) $cards,
				'cart_total'          => $cart_total,
				'place_order_enabled' => $save_card,
				'instalments'         => WC_EBANX_Constants::COUNTRY_BRAZIL === $country ? 'Número de parcelas' : 'Meses sin intereses',
				'id'                  => $this->id,
				'add_tax'             => $this->configs->get_setting_or_default( 'add_iof_to_local_amount_enabled', 'yes' ) === 'yes',
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);
	}
}
