<?php

if (!defined('ABSPATH')) {
	exit;
}

abstract class WC_EBANX_Credit_Card_Gateway extends WC_EBANX_Gateway
{
	/**
	 * The rates for each instalment
	 *
	 * @var array
	 */
	protected $instalment_rates = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->api_name = '_creditcard';

		parent::__construct();

		add_action('woocommerce_order_edit_status', array($this, 'capture_payment_action'), 10, 2);

		if ($this->get_setting_or_default('interest_rates_enabled', 'no') == 'yes') {
			$max_instalments = $this->configs->settings['credit_card_instalments'];
			for ($i=1; $i <= $max_instalments; $i++) {
				$field = 'interest_rates_' . sprintf("%02d", $i);
				$this->instalment_rates[$i] = 0;
				if (is_numeric($this->configs->settings[$field])) {
					$this->instalment_rates[$i] = $this->configs->settings[$field] / 100;
				}
			}
		}
	}

	/**
	 * Check the Auto Capture
	 *
	 * @param  array $actions
	 * @return array
	 */
	public function auto_capture($actions) {
		if (is_array($actions)) {
			$actions['custom_action'] = __('Capture by EBANX');
		}

		return $actions;
	}

	/**
	 * Action to capture the payment
	 *
	 * @return void
	 */
	public function capture_payment_action($order_id, $status) {
		$action = WC_EBANX_Request::read('action');
		$order = wc_get_order($order_id);
		$recapture = false;

		if ($order->payment_method !== $this->id
			|| $status !== 'processing'
			|| $action !== 'woocommerce_mark_order_status') {
			return;
		}

		\Ebanx\Config::set(array(
			'integrationKey' => $this->private_key,
			'testMode' => $this->is_sandbox_mode,
			'directMode' => true,
		));

		$response = \Ebanx\Ebanx::doCapture(array('hash' => get_post_meta($order->id, '_ebanx_payment_hash', true)));
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
	 * Insert the necessary assets on checkout page
	 *
	 * @return void
	 */
	public function checkout_assets()
	{
		if (is_checkout()) {
			wp_enqueue_script('wc-credit-card-form');
			// Using // to avoid conflicts between http and https protocols
			wp_enqueue_script('ebanx', '//js.ebanx.com/ebanx-1.5.min.js', '', null, true);
			wp_enqueue_script('woocommerce_ebanx_jquery_mask', plugins_url('assets/js/jquery-mask.js', WC_EBANX::DIR), array('jquery'), WC_EBANX::get_plugin_version(), true);
			wp_enqueue_script('woocommerce_ebanx_credit_card', plugins_url('assets/js/credit-card.js', WC_EBANX::DIR), array('jquery-payment', 'ebanx'), WC_EBANX::get_plugin_version(), true);

			// If we're on the checkout page we need to pass ebanx.js the address of the order.
			if (is_checkout_pay_page() && isset($_GET['order']) && isset($_GET['order_id'])) {
				$order_key = urldecode($_GET['order']);
				$order_id = absint($_GET['order_id']);
				$order = wc_get_order($order_id);

				if ($order->id === $order_id && $order->order_key === $order_key) {
					static::$ebanx_params['billing_first_name'] = $order->billing_first_name;
					static::$ebanx_params['billing_last_name'] = $order->billing_last_name;
					static::$ebanx_params['billing_address_1'] = $order->billing_address_1;
					static::$ebanx_params['billing_address_2'] = $order->billing_address_2;
					static::$ebanx_params['billing_state'] = $order->billing_state;
					static::$ebanx_params['billing_city'] = $order->billing_city;
					static::$ebanx_params['billing_postcode'] = $order->billing_postcode;
					static::$ebanx_params['billing_country'] = $order->billing_country;
				}
			}
		}

		parent::checkout_assets();
	}

	/**
	 * Mount the data to send to EBANX API
	 *
	 * @param WC_Order $order
	 * @return array
	 * @throws Exception
	 */
	protected function request_data($order)
	{

		if (empty(WC_EBANX_Request::read('ebanx_token', null))
			|| empty(WC_EBANX_Request::read('ebanx_masked_card_number', null))
			|| empty(WC_EBANX_Request::read('ebanx_brand', null))
			|| empty(WC_EBANX_Request::read('ebanx_billing_cvv', null))
		) {
			throw new Exception('MISSING-CARD-PARAMS');
		}

		if (empty(WC_EBANX_Request::read('ebanx_is_one_click', null)) && empty(WC_EBANX_Request::read('ebanx_device_fingerprint', null))) {
			throw new Exception('MISSING-DEVICE-FINGERPRINT');
		}

		$data = parent::request_data($order);

		if (in_array($this->getTransactionAddress('country'), WC_EBANX_Constants::$CREDIT_CARD_COUNTRIES)) {
			$data['payment']['instalments'] = '1';

			if ($this->configs->settings['credit_card_instalments'] > 1 && WC_EBANX_Request::has('ebanx_billing_instalments')) {
				$data['payment']['instalments'] = WC_EBANX_Request::read('ebanx_billing_instalments');
			}
		}

		if (!empty(WC_EBANX_Request::read('ebanx_device_fingerprint', null))) {
			$data['device_id'] = WC_EBANX_Request::read('ebanx_device_fingerprint');
		}

		$data['payment']['payment_type_code'] = WC_EBANX_Request::read('ebanx_brand');
		$data['payment']['creditcard'] = array(
			'token' => WC_EBANX_Request::read('ebanx_token'),
			'card_cvv' => WC_EBANX_Request::read('ebanx_billing_cvv'),
			'auto_capture' => ($this->configs->settings['capture_enabled'] === 'yes'),
		);

		return $data;
	}

	/**
	 * Process the response of request from EBANX API
	 *
	 * @param  Object $request The result of request
	 * @param  WC_Order $order   The order created
	 * @return void
	 */
	protected function process_response($request, $order)
	{
		if ($request->status == 'ERROR' || !$request->payment->pre_approved) {
			return $this->process_response_error($request, $order);
		}

		parent::process_response($request, $order);
	}

	/**
	 * Save order's meta fields for future use
	 *
	 * @param  WC_Order $order The order created
	 * @param  Object $request The request from EBANX success response
	 * @return void
	 */
	protected function save_order_meta_fields($order, $request)
	{
		parent::save_order_meta_fields($order, $request);

		update_post_meta($order->id, '_cards_brand_name', $request->payment->payment_type_code);
		update_post_meta($order->id, '_instalments_number', $request->payment->instalments);
		update_post_meta($order->id, '_masked_card_number', WC_EBANX_Request::read('ebanx_masked_card_number'));
	}

	/**
	 * Save user's meta fields for future use
	 *
	 * @param  WC_Order $order The order created
	 * @return void
	 */
	protected function save_user_meta_fields($order)
	{
		parent::save_user_meta_fields($order);

		if ( ! $this->user_id ) {
			$this->user_id = $order->user_id;
		}

		if ( ! $this->user_id
			|| $this->get_setting_or_default( 'save_card_data', 'no' ) !== 'yes'
			|| ! WC_EBANX_Request::has( 'ebanx-save-credit-card' )
			|| WC_EBANX_Request::read( 'ebanx-save-credit-card' ) !== 'yes' ) {
			return;
		}

		$cards = get_user_meta( $this->user_id, '_ebanx_credit_card_token', true );
		$cards = !empty($cards) ? $cards : [];

		$card = new \stdClass();

		$card->brand = WC_EBANX_Request::read('ebanx_brand');
		$card->token = WC_EBANX_Request::read('ebanx_token');
		$card->masked_number = WC_EBANX_Request::read('ebanx_masked_card_number');

		foreach ($cards as $cd) {
			if (empty($cd)) {
				continue;
			}

			if ($cd->masked_number == $card->masked_number && $cd->brand == $card->brand) {
				$cd->token = $card->token;
				unset($card);
			}
		}

		if (isset($card)) {
			$cards[] = $card;
		}

		update_user_meta( $this->user_id, '_ebanx_credit_card_token', $cards );
	}

	/**
	 * The main method to process the payment came from WooCommerce checkout
	 * This method check the informations sent by WooCommerce and if them are fine, it sends the request to EBANX API
	 * The catch captures the errors and check the code sent by EBANX API and then show to the users the right error message
	 *
	 * @param  integer $order_id    The ID of the order created
	 * @return void
	 */
	public function process_payment($order_id)
	{
		$has_instalments = (WC_EBANX_Request::has('ebanx_billing_instalments') || WC_EBANX_Request::has('ebanx-credit-card-installments'));

		if ( $has_instalments ) {

			$order = wc_get_order( $order_id );

			$total_price = get_post_meta($order_id, '_order_total', true);
			$tax_rate = 0;
			$instalments = WC_EBANX_Request::has('ebanx_billing_instalments') ? WC_EBANX_Request::read('ebanx_billing_instalments') : WC_EBANX_Request::read('ebanx-credit-card-installments');

			if ( array_key_exists( $instalments, $this->instalment_rates ) ) {
				$tax_rate = $this->instalment_rates[$instalments];
			}

			$total_price += $total_price * $tax_rate;
			update_post_meta($order_id, '_order_total', $total_price);
		}

		return parent::process_payment($order_id);
	}

	/**
	 * Checks if the payment term is allowed based on price, country and minimal instalment value
	 *
	 * @param doubloe $price Product price used as base
	 * @param int $instalment_number Number of instalments
	 * @param string $country Costumer country
	 * @return integer
	 */
	public function is_valid_instalment_amount($price, $instalment_number, $country = null) {
		if ($instalment_number === 1) {
			return true;
		}

		$country = $country ?: WC()->customer->get_country();
		$currency_code = strtolower($this->merchant_currency);

		switch (trim(strtolower($country))) {
			case 'br':
				$site_to_local_rate = $this->get_local_currency_rate_for_site(WC_EBANX_Constants::CURRENCY_CODE_BRL);
				$merchant_min_instalment_value = $this->get_setting_or_default("min_instalment_value_$currency_code", 0) * $site_to_local_rate;
				$min_instalment_value = max(
					WC_EBANX_Constants::ACQUIRER_MIN_INSTALMENT_VALUE_BRL,
					$merchant_min_instalment_value
				);
				break;
			case 'mx':
				$site_to_local_rate = $this->get_local_currency_rate_for_site(WC_EBANX_Constants::CURRENCY_CODE_MXN);
				$merchant_min_instalment_value = $this->get_setting_or_default("min_instalment_value_$currency_code", 0) * $site_to_local_rate;
				$min_instalment_value = max(
					WC_EBANX_Constants::ACQUIRER_MIN_INSTALMENT_VALUE_MXN,
					$merchant_min_instalment_value
				);
				break;
			case 'co':
				$site_to_local_rate = $this->get_local_currency_rate_for_site(WC_EBANX_Constants::CURRENCY_CODE_COP);
				$merchant_min_instalment_value = $this->get_setting_or_default("min_instalment_value_$currency_code", 0) * $site_to_local_rate;
				$min_instalment_value = max(
					WC_EBANX_Constants::ACQUIRER_MIN_INSTALMENT_VALUE_COP,
					$merchant_min_instalment_value
				);
				break;
			case 'ar':
				$site_to_local_rate = $this->get_local_currency_rate_for_site(WC_EBANX_Constants::CURRENCY_CODE_ARS);
				$merchant_min_instalment_value = $this->get_setting_or_default("min_instalment_value_$currency_code", 0) * $site_to_local_rate;
				$min_instalment_value = max(
					WC_EBANX_Constants::ACQUIRER_MIN_INSTALMENT_VALUE_ARS,
					$merchant_min_instalment_value
				);
				break;
		}

		if (isset($site_to_local_rate) && isset($min_instalment_value)) {
			$local_value = $price * $site_to_local_rate;
			$instalment_value = $local_value / $instalment_number;
			return $instalment_value >= $min_instalment_value;
		}

		return false;
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param  WC_Order $order The order created
	 * @return void
	 */
	public static function thankyou_page($order)
	{
		$order_amount = $order->get_total();
		$instalments_number = get_post_meta($order->id, '_instalments_number', true) ?: 1;
		$country = trim(strtolower(get_post_meta($order->id, '_billing_country', true)));
		$currency = $order->get_order_currency();

		if ($country === WC_EBANX_Constants::COUNTRY_BRAZIL) {
			$order_amount += round(($order_amount * WC_EBANX_Constants::BRAZIL_TAX), 2);
		}

		$data = array(
			'data' => array(
				'card_brand_name' => get_post_meta($order->id, '_cards_brand_name', true),
				'instalments_number' => $instalments_number,
				'instalments_amount' => wc_price(round($order_amount / $instalments_number, 2), array('currency' => $currency)),
				'masked_card' => substr(get_post_meta($order->id, '_masked_card_number', true), -4),
				'customer_email' => $order->billing_email,
				'customer_name' => $order->billing_first_name,
				'total' => wc_price( $order_amount, array( 'currency' => $currency ) ),
				'hash' => get_post_meta( $order->id, '_ebanx_payment_hash', true ),
			),
			'order_status' => $order->get_status(),
			'method' => $order->payment_method
		);

		parent::thankyou_page($data);
	}

	/**
	 * Calculates the interests and values of items based on interest rates settings
	 *
	 * @param int $amount      The total of the user cart
	 * @param int $max_instalments The max number of instalments based on settings
	 * @param int $tax The tax applied
	 * @return filtered array       An array of instalment with price, amount, if it has interests and the number
	 */
	public function get_payment_terms($amount, $max_instalments, $tax = 0) {
		$instalments = array();
		$instalment_taxes = $this->instalment_rates;

		for ($number = 1; $number <= $max_instalments; ++$number) {
			$has_interest = false;
			$cart_total = $amount;

			if (isset($instalment_taxes) && array_key_exists($number, $instalment_taxes)) {
				$cart_total += $cart_total * $instalment_taxes[$number];
				$cart_total += $cart_total * $tax;
				if ($instalment_taxes[$number] > 0) {
					$has_interest = true;
				}
			}

			if ( $this->is_valid_instalment_amount($cart_total, $number) ) {
				$instalment_price = $cart_total / $number;
				$instalment_price = round(floatval($instalment_price), 2);

				$instalments[] = array(
					'price' => $instalment_price,
					'has_interest' => $has_interest,
					'number' => $number
				);
			}
		}

		return apply_filters('ebanx_get_payment_terms', $instalments);
	}

	/**
	 * The HTML structure on checkout page
	 */
	public function payment_fields() {
		$cart_total = $this->get_order_total();

		$cards = array();

		$save_card = $this->get_setting_or_default('save_card_data', 'no') === 'yes';

		if ( $save_card ) {
			$cards = array_filter( (array) get_user_meta( $this->user_id, '_ebanx_credit_card_token', true ), function ( $card ) {
				return !empty($card->brand) && !empty($card->token) && !empty($card->masked_number);
			});
		}

		$country = $this->getTransactionAddress('country');

		$max_instalments = min(
			$this->configs->settings['credit_card_instalments'],
			WC_EBANX_Constants::$MAX_INSTALMENTS[$country]
		);

		$tax = 0;
		if ( get_woocommerce_currency() === WC_EBANX_Constants::CURRENCY_CODE_BRL
			&& $this->configs->get_setting_or_default('add_iof_to_local_amount_enabled', 'yes') === 'yes' ) {
			$tax = WC_EBANX_Constants::CURRENCY_CODE_BRL;
		}

		$instalments_terms = $this->get_payment_terms($cart_total, $max_instalments, $tax);

		$currency = WC_EBANX_Constants::$LOCAL_CURRENCIES[$country];

		$message = $this->get_sandbox_form_message( $country );
		wc_get_template(
			'sandbox-checkout-alert.php',
			array(
				'is_sandbox_mode' => $this->is_sandbox_mode,
				'message' => $message,
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);

		wc_get_template(
			$this->id . '/payment-form.php',
			array(
				'currency' => $currency,
				'country' => $country,
				'instalments_terms' => $instalments_terms,
				'currency' => $this->currency_code,
				'currency_rate' => round(floatval($this->get_local_currency_rate_for_site($this->currency_code)), 2),
				'cards' => (array) $cards,
				'cart_total' => $cart_total,
				'place_order_enabled' => $save_card,
				'instalments' => $country === WC_EBANX_Constants::COUNTRY_BRAZIL ? 'Número de parcelas' :  'Meses sin intereses',
				'id' => $this->id,
				'add_tax' => $this->configs->get_setting_or_default('add_iof_to_local_amount_enabled', 'yes') === 'yes',
			),
			'woocommerce/ebanx/',
			WC_EBANX::get_templates_path()
		);
	}
}
