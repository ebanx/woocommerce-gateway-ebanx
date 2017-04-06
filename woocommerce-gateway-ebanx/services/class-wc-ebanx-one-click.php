<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class WC_EBANX_One_Click {
	private $cards;
	private $userId;
	private $gateway;
	private $orderAction = 'ebanx_create_order';

	protected $instalment_rates = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->userId  = get_current_user_id();
		$this->userCountry = trim(strtolower(get_user_meta( $this->userId, 'billing_country', true )));

		$this->gateway = $this->userCountry ? ($this->userCountry === WC_EBANX_Constants::COUNTRY_BRAZIL ? new WC_EBANX_Credit_Card_BR_Gateway() : new WC_EBANX_Credit_Card_MX_Gateway()) : false;

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );
		add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'print_button' ) );

		add_action( 'wp_loaded', array( $this, 'one_click_handler' ), 99 );

		$cards = get_user_meta( $this->userId, '_ebanx_credit_card_token', true );

		$this->cards = is_array( $cards ) ? array_filter( $cards ) : array();

		if ($this->gateway && $this->gateway->get_setting_or_default('interest_rates_enabled', 'no') == 'yes') {
			$max_instalments = $this->gateway->configs->settings['credit_card_instalments'];
			for ($i=1; $i <= $max_instalments; $i++) {
				$field = 'interest_rates_' . sprintf("%02d", $i);
				$this->instalment_rates[$i] = 0;
				if (is_numeric($this->gateway->configs->settings[$field])) {
					$this->instalment_rates[$i] = $this->gateway->configs->settings[$field] / 100;
				}
			}
		}
	}

	/**
	 * It cleans the cart after generate a new URL
	 *
	 * @return void
	 */
	public function empty_cart() {
		$cart = WC()->session->get( 'cart' );
		update_user_meta( $this->userId, '_ebanx_persistent_cart', $cart );

		WC()->cart->empty_cart( true );
	}

	/**
	 * Process the one click request
	 *
	 * @return void
	 */
	public function one_click_handler() {
		ob_start();

		if ( is_admin()
			|| ! WC_EBANX_Request::has('product')
			|| ! WC_EBANX_Request::has('ebanx-action')
			|| WC_EBANX_Request::read('ebanx-action') !== $this->orderAction
			|| ! WC_EBANX_Request::has('ebanx-nonce') 
			|| ! wp_verify_nonce( WC_EBANX_Request::read('ebanx-nonce'), $this->orderAction )
			|| ! WC_EBANX_Request::has('ebanx-one-click-token') 
			|| ! WC_EBANX_Request::has('ebanx-one-click-cvv') 
			|| ! WC_EBANX_Request::has('ebanx-credit-card-installments')
			|| ! WC_EBANX_Request::has('ebanx-cart-total')
			|| ! WC_EBANX_Request::has('ebanx-product-id')
			|| ! $this->customer_can() 
			|| ! $this->customer_has_ebanx_required_data()
		) {
			return;
		}

		try {
			$order = wc_create_order(array(
				'status' => 'pending',
				'customer_id' => $this->userId
			));

			$product_price = WC_EBANX_Request::read('ebanx-cart-total');
			$product_id = WC_EBANX_Request::read('ebanx-product-id');

			$user = array(
				'email' => WC()->customer->get_billing_email(),	
				'country' => WC()->customer->get_billing_country(),
				'first_name' => WC()->customer->get_billing_first_name(),
				'last_name' => WC()->customer->get_billing_last_name(),
				'company' => WC()->customer->get_billing_company(),
				'address_1' => WC()->customer->get_billing_address_1(),
				'address_2' => WC()->customer->get_billing_address_2(),
				'city' => WC()->customer->get_billing_city(),
				'state' => WC()->customer->get_billing_state(),
				'postcode' => WC()->customer->get_billing_postcode(),
				'phone' => WC()->customer->get_billing_phone()
			);

			$product_to_add = get_product( $product_id );
			$sale_price = $product_to_add->get_price();

			$order->add_product( $product_to_add, 1 );
			$order->set_billing_email($user['email']);
			$order->set_billing_country($user['country']);
			$order->set_billing_first_name($user['first_name']);
			$order->set_billing_last_name($user['last_name']);
			$order->set_billing_company($user['company']);
			$order->set_billing_address_1($user['address_1']);
			$order->set_billing_address_2($user['address_2']);
			$order->set_billing_city($user['city']);
			$order->set_billing_state($user['state']);
			$order->set_billing_phone($user['phone']);
			$order->set_payment_method($this->gateway);
			$order->set_total( WC()->cart->shipping_total, 'shipping' );
			$order->set_total( WC()->cart->get_cart_discount_total(), 'cart_discount' );
			$order->set_total( WC()->cart->get_cart_discount_tax_total(), 'cart_discount_tax' );
			$order->set_total( WC()->cart->tax_total, 'tax' );
			$order->set_total( WC()->cart->shipping_tax_total, 'shipping_tax' );
			$order->set_total( WC()->cart->total );
			$order->calculate_totals();

			$response = $this->gateway->process_payment($order->id);

			if ($response['result'] !== 'success') {
				$message = __('EBANX: Unable to create the payment via one click.', 'woocommerce-gateway-ebanx');

				$order->add_order_note($message);
				
				throw new Exception($message);
			}

			$this->restore_cart();

			wp_safe_redirect($response['redirect']);
			exit;
		}
		catch (Exception $e) {
			// TODO: Make a caucght exception
		}

		$this->restore_cart();

		return;
	}

	/**
	 * Restore the items of the cart until the last request
	 *
	 * @return void
	 */
	public function restore_cart() {
		// delete current cart
		WC()->cart->empty_cart( true );

		// update user meta with saved persistent
		$saved_cart = get_user_meta( $this->userId, '_ebanx_persistent_cart', true );

		// then reload cart
		WC()->session->set( 'cart', $saved_cart );
		WC()->cart->get_cart_from_session();
	}

	/**
	 * It creates the user's billing data to process the one click response
	 *
	 * @return array
	 */
	public function get_user_billing_address() {
		// Formatted Addresses
		$billing = array(
			'first_name' => get_user_meta( $this->userId, 'billing_first_name', true ),
			'last_name'  => get_user_meta( $this->userId, 'billing_last_name', true ),
			'company'    => get_user_meta( $this->userId, 'billing_company', true ),
			'address_1'  => get_user_meta( $this->userId, 'billing_address_1', true ),
			'address_2'  => get_user_meta( $this->userId, 'billing_address_2', true ),
			'city'       => get_user_meta( $this->userId, 'billing_city', true ),
			'state'      => get_user_meta( $this->userId, 'billing_state', true ),
			'postcode'   => get_user_meta( $this->userId, 'billing_postcode', true ),
			'country'    => get_user_meta( $this->userId, 'billing_country', true ),
			'email'      => get_user_meta( $this->userId, 'billing_email', true ),
			'phone'      => get_user_meta( $this->userId, 'billing_phone', true )
		);

		if ( ! empty( $billing['country'] ) ) {
			WC()->customer->set_billing_country( $billing['country'] );
		}
		if ( ! empty( $billing['state'] ) ) {
			WC()->customer->set_billing_state( $billing['state'] );
		}
		if ( ! empty( $billing['postcode'] ) ) {
			WC()->customer->set_billing_postcode( $billing['postcode'] );
		}

		return apply_filters( 'ebanx_customer_billing', array_filter( $billing ) );
	}

	/**
	 * Get the user's shipping address by user id
	 *
	 * @param integer $id User ID
	 * @return array
	 */
	public function get_user_shipping_address( $id ) {

		if ( ! WC()->cart->needs_shipping_address() ) {
			return array();
		}

		// Formatted Addresses
		$shipping = array(
			'first_name' => get_user_meta( $id, 'shipping_first_name', true ),
			'last_name'  => get_user_meta( $id, 'shipping_last_name', true ),
			'company'    => get_user_meta( $id, 'shipping_company', true ),
			'address_1'  => get_user_meta( $id, 'shipping_address_1', true ),
			'address_2'  => get_user_meta( $id, 'shipping_address_2', true ),
			'city'       => get_user_meta( $id, 'shipping_city', true ),
			'state'      => get_user_meta( $id, 'shipping_state', true ),
			'postcode'   => get_user_meta( $id, 'shipping_postcode', true ),
			'country'    => get_user_meta( $id, 'shipping_country', true )
		);

		return apply_filters( 'ebanx_customer_shipping', array_filter( $shipping ) );
	}

	/**
	 * Set shipping info by user's information
	 *
	 * @param array   $values The user's information
	 */
	public function set_shipping_info( $values ) {

		// Update customer location to posted location so we can correctly check available shipping methods
		if ( ! empty( $values['country'] ) ) {
			WC()->customer->set_billing_shipping_country( $values['country'] );
		}
		if ( ! empty( $values['state'] ) ) {
			WC()->customer->set_billing_shipping_state( $values['state'] );
		}
		if ( ! empty( $values['postcode'] ) ) {
			WC()->customer->set_billing_shipping_postcode( $values['postcode'] );
		}
	}

	/**
	 * Set the assets necessary by one click works
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'woocommerce_ebanx_one_click_script',
			plugins_url( 'assets/js/one-click.js', WC_EBANX::DIR ),
			array(),
			WC_EBANX::get_plugin_version(),
			true
		);

		wp_enqueue_style(
			'woocommerce_ebanx_one_click_style',
			plugins_url( 'assets/css/one-click.css', WC_EBANX::DIR )
		);
	}

	/**
	 * Check if the custom has all required data required by EBANX
	 *
	 * @return boolean If the user has all required data, return true
	 */
	protected function customer_has_ebanx_required_data() {
		$card = current( array_filter( (array) array_filter( get_user_meta( $this->userId, '_ebanx_credit_card_token', true ) ), function ( $card ) {
			return $card->token == WC_EBANX_Request::read('ebanx-one-click-token');
		} ) );

		$names = $this->gateway->names;

		$_POST['ebanx_token'] = $card->token;
		$_POST['ebanx_masked_card_number'] = $card->masked_number;
		$_POST['ebanx_brand'] = $card->brand;
		$_POST['ebanx_billing_cvv'] = WC_EBANX_Request::read('ebanx-one-click-cvv');
		$_POST['ebanx_is_one_click'] = true;
		$_POST['ebanx_billing_instalments'] = WC_EBANX_Request::read('ebanx-credit-card-installments');

		$_POST[$names['ebanx_billing_brazil_document']] = get_user_meta( $this->userId, '_ebanx_billing_brazil_document', true );
		$_POST[$names['ebanx_billing_brazil_birth_date']] = get_user_meta( $this->userId, '_ebanx_billing_brazil_birth_date', true );

		$_POST['billing_postcode']  = $this->get_user_billing_address()['postcode'];
		$_POST['billing_address_1'] = $this->get_user_billing_address()['address_1'];
		$_POST['billing_city']      = $this->get_user_billing_address()['city'];
		$_POST['billing_state']     = $this->get_user_billing_address()['state'];

		return empty( WC_EBANX_Request::read('ebanx-one-click-token') )
			|| empty( WC_EBANX_Request::read('ebanx-credit-card-installments') )
			|| empty( WC_EBANX_Request::read('ebanx-one-click-cvv') )
			|| empty( WC_EBANX_Request::read('ebanx-action') )
			|| ! WC_EBANX_Request::has($names['ebanx_billing_brazil_document'])
			|| ! WC_EBANX_Request::has($names['ebanx_billing_brazil_birth_date'])
			|| empty( WC_EBANX_Request::read($names['ebanx_billing_brazil_document']) )
			|| empty( WC_EBANX_Request::read($names['ebanx_billing_brazil_birth_date']) );
	}

	/**
	 * Check if the customer is ready
	 *
	 * @return [type] [description]
	 */
	public function customer_can() {
		return !is_user_logged_in() || !get_user_meta( $this->userId, '_billing_email', true ) && !empty( $this->cards );
	}

	/**
	 * Render the button "One-Click Purchase" using a template
	 *
	 * @return void
	 */
	public function print_button() {
		global $product;

		switch ( get_locale() ) {
			case 'pt_BR':
				$messages = array(
					'instalments' => 'Número de parcelas',
				);
				break;
			case 'es_ES':
			case 'es_CO':
			case 'es_CL':
			case 'es_PE':
			case 'es_MX':
				$messages = array(
					'instalments' => 'Meses sin intereses'
				);
				break;
			default:
				$messages = array(
					'instalments' => 'Number of installments',
				);
				break;
		}

		$cart_total = $product->price;

		$max_instalments = min($this->gateway->configs->settings['credit_card_instalments'], $this->gateway->fetch_acquirer_max_installments_for_price($cart_total, 'br'));

		$instalments_terms = $this->gateway->get_payment_terms($cart_total, $max_instalments);

		$args = apply_filters( 'ebanx_template_args', array(
				'cards' => $this->cards,
				'cart_total' => $cart_total,
				'product_id' => $product->id,
				'max_instalments' => $max_instalments,
				'installment_taxes' => $this->instalment_rates,
				'label' => __( 'Pay with one click', 'woocommerce-gateway-ebanx' ),
				'instalments' => $messages['instalments'],
				'instalments_terms' => $instalments_terms,
				'nonce' => wp_create_nonce( $this->orderAction ),
				'action' => $this->orderAction,
				'permalink' => get_permalink($product->id)
			) );

		wc_get_template( 'one-click.php', $args, '', WC_EBANX::get_templates_path() . 'one-click/' );
	}
}

new WC_EBANX_One_Click();
