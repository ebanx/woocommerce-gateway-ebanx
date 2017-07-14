<?php

require WC_EBANX_VENDOR_DIR . 'ebanx-php/src/autoload.php';

if (!defined('ABSPATH')) {
	exit;
}

// Update converted value via ajax
add_action('wp_ajax_nopriv_ebanx_update_converted_value', 'ebanx_update_converted_value');
add_action('wp_ajax_ebanx_update_converted_value', 'ebanx_update_converted_value');

/**
 * It's a just a method to call `ebanx_update_converted_value`
 * to avoid WordPress hooks problem
 *
 * @return void
 */
function ebanx_update_converted_value () {
	$gateway = new WC_EBANX_Gateway();

	echo $gateway->checkout_rate_conversion(
		WC_EBANX_Request::read('currency'),
		false,
		WC_EBANX_Request::read('country'),
		WC_EBANX_Request::read('instalments')
	);

	wp_die();
}

class WC_EBANX_Gateway extends WC_Payment_Gateway
{
	protected static $ebanx_params = array();
	protected static $initializedGateways = 0;
	protected static $totalGateways = 0;

	const REQUIRED_MARK = " <abbr class=\"required\" title=\"required\">*</abbr>";

	/**
	 * Constructor
	 */
	public function __construct()
	{
		self::$totalGateways++;

		$this->userId = get_current_user_id();

		$this->configs = new WC_EBANX_Global_Gateway();

		$this->is_sandbox_mode = ($this->configs->settings['sandbox_mode_enabled'] === 'yes');

		$this->private_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_private_key'] : $this->configs->settings['live_private_key'];

		$this->public_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_public_key'] : $this->configs->settings['live_public_key'];

		if ($this->configs->settings['debug_enabled'] === 'yes') {
			$this->log = new WC_Logger();
		}

		add_action('wp_enqueue_scripts', array($this, 'checkout_assets'), 100);

		add_filter('woocommerce_checkout_fields', array($this, 'checkout_fields'));

		$this->supports = array(
			// 'subscriptions',
			'refunds',
		);

		$this->icon = $this->show_icon();

		$this->names = $this->get_billing_field_names();

		$this->merchant_currency = strtoupper(get_woocommerce_currency());
	}

	/**
	 * Sets up the pay api to be called during the plugin lifecycle
	 *
	 * @return void
	 */
	private function setup_pay_api() {
		\Ebanx\Config::set([
			'integrationKey' => $this->private_key,
			'testMode' => $this->is_sandbox_mode
		]);
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 */
	public function is_available()
	{
		$currency = $this->merchant_currency;

		$this->language = $this->getTransactionAddress('country');

		return parent::is_available()
			&& $this->enabled === 'yes'
			&& !empty($this->public_key)
			&& !empty($this->private_key)
			&& ($this->currency_is_usd_eur($currency)
			|| $this->ebanx_process_merchant_currency($currency)
			);
	}

	/**
	 * Check if the currency is processed by EBANX
	 * @param  string $currency Possible currencies: BRL, USD, EUR, PEN, CLP, COP, MXN
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function ebanx_process_merchant_currency($currency) {
		return $currency;
	}

	/**
	 * General method to check if the currency is USD or EUR. These currencies are accepted by all payment methods.
	 *
	 * @param  string $currency Possible currencies: USD, EUR
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function currency_is_usd_eur($currency) {
		return in_array($currency, array(WC_EBANX_Constants::CURRENCY_CODE_USD, WC_EBANX_Constants::CURRENCY_CODE_EUR));
	}

	/**
	 * Insert custom billing fields on checkout page
	 *
	 * @param  array $fields WooCommerce's fields
	 * @return array         The new fields
	 */
	public function checkout_fields($fields) {
		$fields_options = array();
		if (isset($this->configs->settings['brazil_taxes_options']) && is_array($this->configs->settings['brazil_taxes_options'])) {
			$fields_options = $this->configs->settings['brazil_taxes_options'];
		}

		$disable_own_fields = isset($this->configs->settings['checkout_manager_enabled']) && $this->configs->settings['checkout_manager_enabled'] === 'yes';

		$cpf = get_user_meta($this->userId, '_ebanx_billing_brazil_document', true);
		$birth_date_br = get_user_meta($this->userId, '_ebanx_billing_brazil_birth_date', true);

		$cnpj = get_user_meta($this->userId, '_ebanx_billing_brazil_cnpj', true);

		$rut = get_user_meta($this->userId, '_ebanx_billing_chile_document', true);
		$birth_date_cl = get_user_meta($this->userId, '_ebanx_billing_chile_birth_date', true);

		$dni = get_user_meta($this->userId, '_ebanx_billing_colombia_document', true);

		$ebanx_billing_brazil_person_type = array(
			'type' => 'select',
			'label' => __('Select an option', 'woocommerce-gateway-ebanx'),
			'default' => 'cpf',
			'class' => array('ebanx_billing_brazil_selector', 'ebanx-select-field'),
			'options' => array(
				'cpf' => __('CPF - Individuals', 'woocommerce-gateway-ebanx'),
				'cnpj' => __('CNPJ - Companies', 'woocommerce-gateway-ebanx')
			)
		);

		$ebanx_billing_brazil_birth_date = array(
			'type'  => 'text',
			'label' => __('Birth Date', 'woocommerce-gateway-ebanx') . self::REQUIRED_MARK,
			'class' => array('ebanx_billing_brazil_birth_date', 'ebanx_billing_brazil_cpf', 'ebanx_billing_brazil_selector_option', 'form-row-wide'),
			'default' => isset($birth_date_br) ? $birth_date_br : ''
		);
		$ebanx_billing_brazil_document = array(
			'type'     => 'text',
			'label'    => 'CPF' . self::REQUIRED_MARK,
			'class' => array('ebanx_billing_brazil_document', 'ebanx_billing_brazil_cpf', 'ebanx_billing_brazil_selector_option', 'form-row-wide'),
			'default' => isset($cpf) ? $cpf : ''
		);

		$ebanx_billing_brazil_cnpj = array(
			'type'     => 'text',
			'label'    => 'CNPJ' . self::REQUIRED_MARK,
			'class' => array('ebanx_billing_brazil_cnpj', 'ebanx_billing_brazil_cnpj', 'ebanx_billing_brazil_selector_option', 'form-row-wide'),
			'default' => isset($cnpj) ? $cnpj : ''
		);

		$ebanx_billing_chile_birth_date = array(
			'type'  => 'text',
			'label' => __('Birth Date', 'woocommerce-gateway-ebanx') . self::REQUIRED_MARK,
			'class' => array('ebanx_billing_chile_birth_date', 'form-row-wide'),
			'default' => isset($birth_date_cl) ? $birth_date_cl : ''
		);
		$ebanx_billing_chile_document = array(
			'type'     => 'text',
			'label'    => 'RUT' . self::REQUIRED_MARK,
			'class' => array('ebanx_billing_chile_document', 'form-row-wide'),
			'default' => isset($rut) ? $rut : ''
		);
		$ebanx_billing_colombia_document = array(
			'type'     => 'text',
			'label'    => 'DNI' . self::REQUIRED_MARK,
			'class' => array('ebanx_billing_colombia_document', 'form-row-wide'),
			'default' => isset($dni) ? $dni : ''
		);

		if (!$disable_own_fields) {
			// CPF and CNPJ are enabled
			if (in_array('cpf', $fields_options) && in_array('cnpj', $fields_options)) {
				$fields['billing']['ebanx_billing_brazil_person_type'] = $ebanx_billing_brazil_person_type;
			}

			// CPF is enabled
			if (in_array('cpf', $fields_options)) {
				$fields['billing']['ebanx_billing_brazil_document'] = $ebanx_billing_brazil_document;

				$fields['billing']['ebanx_billing_brazil_birth_date'] = $ebanx_billing_brazil_birth_date;
			}

			// CNPJ is enabled
			if (in_array('cnpj', $fields_options)) {
				$fields['billing']['ebanx_billing_brazil_cnpj'] = $ebanx_billing_brazil_cnpj;
			}

			// For Chile
			$fields['billing']['ebanx_billing_chile_document'] = $ebanx_billing_chile_document;
			$fields['billing']['ebanx_billing_chile_birth_date'] = $ebanx_billing_chile_birth_date;

			// For Colombia
			$fields['billing']['ebanx_billing_colombia_document'] = $ebanx_billing_colombia_document;
		}

		return $fields;
	}

	/**
	 * Fetches the billing field names for compatibility with checkout managers
	 *
	 * @return array
	 */
	public function get_billing_field_names() {
		return array(
			// Brazil General
			'ebanx_billing_brazil_person_type' => $this->get_checkout_manager_settings_or_default('checkout_manager_brazil_person_type', 'ebanx_billing_brazil_person_type'),

			// Brazil CPF
			'ebanx_billing_brazil_document' => $this->get_checkout_manager_settings_or_default('checkout_manager_cpf_brazil', 'ebanx_billing_brazil_document'),
			'ebanx_billing_brazil_birth_date' => $this->get_checkout_manager_settings_or_default('checkout_manager_birthdate', 'ebanx_billing_brazil_birth_date'),

			// Brazil CNPJ
			'ebanx_billing_brazil_cnpj' => $this->get_checkout_manager_settings_or_default('checkout_manager_cnpj_brazil', 'ebanx_billing_brazil_cnpj'),

			// Chile Fields
			'ebanx_billing_chile_document' => $this->get_checkout_manager_settings_or_default('checkout_manager_chile_document', 'ebanx_billing_chile_document'),
			'ebanx_billing_chile_birth_date' => $this->get_checkout_manager_settings_or_default('checkout_manager_chile_birth_date', 'ebanx_billing_chile_birth_date'),

			// Colombia Fields
			'ebanx_billing_colombia_document' => $this->get_checkout_manager_settings_or_default('checkout_manager_colombia_document', 'ebanx_billing_colombia_document'),
		);
	}

	/**
	 * Fetches a single checkout manager setting from the gateway settings if found, otherwise it returns an optional default value
	 *
	 * @param  string $name    The setting name to fetch
	 * @param  mixed  $default The default value in case setting is not present
	 * @return mixed
	 */
	private function get_checkout_manager_settings_or_default($name, $default=null) {
		if (!isset($this->configs->settings['checkout_manager_enabled']) || $this->configs->settings['checkout_manager_enabled'] !== 'yes') {
			return $default;
		}

		return $this->get_setting_or_default($name, $default);
	}

	/**
	 * Fetches a single setting from the gateway settings if found, otherwise it returns an optional default value
	 *
	 * @param  string $name    The setting name to fetch
	 * @param  mixed  $default The default value in case setting is not present
	 * @return mixed
	 */
	public function get_setting_or_default($name, $default=null) {
		return $this->configs->get_setting_or_default($name, $default);
	}

	/**
	 * The icon on the right of the gateway name on checkout page
	 *
	 * @return string The URI of the icon
	 */
	public function show_icon()
	{
		return plugins_url('/assets/images/' . $this->id . '.png', plugin_basename(dirname(__FILE__)));
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
	 * Output the admin settings in the correct format.
	 *
	 * @return void
	 */
	public function admin_options()
	{
		include WC_EBANX_TEMPLATES_DIR . 'views/html-admin-page.php';
	}

	/**
	 * Process a refund created by the merchant
	 * @param  integer $order_id    The id of the order created
	 * @param  int $amount          The amount of the refund
	 * @param  string $reason       Optional description
	 * @return boolean
	 */
	public function process_refund($order_id, $amount = null, $reason = '')
	{
		try {
			$order = wc_get_order($order_id);

			$hash = get_post_meta($order->id, '_ebanx_payment_hash', true);

			do_action('ebanx_before_process_refund', $order, $hash);

			if (!$order || is_null($amount) || !$hash) {
				return false;
			}

			if ( empty($reason) ) {
				$reason = __('No reason specified.', 'woocommerce-gateway-ebanx');
			}

			$data = array(
				'hash'        => $hash,
				'amount'      => $amount,
				'operation'   => 'request',
				'description' => $reason,
			);

			$config = array(
				'integrationKey' => $this->private_key,
				'testMode'       => $this->is_sandbox_mode,
			);

			\Ebanx\Config::set($config);

			$request = \Ebanx\EBANX::doRefund($data);

			if ($request->status !== 'SUCCESS') {
				do_action('ebanx_process_refund_error', $order, $request);

				switch ($request->status_code) {
					case 'BP-REF-7':
						$message = __('The payment cannot be refunded because it is not confirmed.', 'woocommerce-gateway-ebanx');
						break;
					default:
						$message = $request->status_message;
				}

				return new WP_Error('ebanx_process_refund_error', $message);
			}

			$refunds = $request->payment->refunds;

			$order->add_order_note(sprintf(__('EBANX: Refund requested. %s - Refund ID: %s - Reason: %s.', 'woocommerce-gateway-ebanx'), wc_price($amount), $request->refund->id, $reason));

			update_post_meta($order_id, '_ebanx_payment_refunds', $refunds);

			do_action('ebanx_after_process_refund', $order, $request, $refunds);

			return true;
		}
		catch (Exception $e) {
			return new WP_Error('ebanx_process_refund_error', __('We could not finish processing this refund. Please try again.'));
		}
	}

	/**
	 * Queries for a currency exchange rate against site currency
	 *
	 * @param  $local_currency_code string The local currency code to query for
	 * @return double
	 */
	public function get_local_currency_rate_for_site($local_currency_code) {
		if ($this->merchant_currency === strtoupper($local_currency_code)) {
			return 1;
		}

		$usd_to_site_rate = 1;
		$converted_currencies = array(
			WC_EBANX_Constants::CURRENCY_CODE_USD,
			WC_EBANX_Constants::CURRENCY_CODE_EUR
		);

		if ( ! in_array($this->merchant_currency, $converted_currencies) ) {
			$usd_to_site_rate = $this->get_currency_rate($this->merchant_currency);
		}

		return $this->get_currency_rate($local_currency_code) / $usd_to_site_rate;
	}

	/**
	 * Queries for a currency exchange rate against USD
	 *
	 * @param  $local_currency_code string The local currency code to query for
	 * @return double
	 */
	public function get_currency_rate($local_currency_code) {
		$this->setup_pay_api();

		$cache_key = 'EBANX_exchange_'.$local_currency_code;

		// Every five minutes
		$cache_time = date('YmdH').floor(date('i') / 5);

		$cached = get_option($cache_key);
		if ($cached !== false) {
			list($rate, $time) = explode('|', $cached);
			if ($time === $cache_time) {
				return $rate;
			}
		}

		$usd_to_local = \Ebanx\Ebanx::getExchange( array(
			'currency_code' => $this->merchant_currency,
			'currency_base_code' => $local_currency_code
		) );

		if (!isset($usd_to_local)
			|| strtoupper(trim($usd_to_local->status)) !== 'SUCCESS') {

			return 1;
		}

		$rate = $usd_to_local->currency_rate->rate;
		update_option($cache_key, $rate.'|'.$cache_time);
		return $rate;
	}

	/**
	 * Mount the data to send to EBANX API
	 *
	 * @param  WC_Order $order
	 * @return array
	 */
	protected function request_data($order)
	{
		$home_url = esc_url( home_url( '/' ) );

		$has_cpf = false;
		$has_cnpj = false;
		$person_type = 'personal';

		$data = array(
			'mode'      => 'full',
			'operation' => 'request',
			'payment'   => array(
				'notification_url'      => $home_url,
				'redirect_url'          => $home_url,
				'user_value_1'          => 'from_woocommerce',
				'user_value_3'          => 'version=' . WC_EBANX::get_plugin_version(),
				'country'               => $order->billing_country,
				'currency_code'         => $this->merchant_currency,
				'name'                  => $order->billing_first_name . ' ' . $order->billing_last_name,
				'email'                 => $order->billing_email,
				'phone_number'          => $order->billing_phone,
				'amount_total'          => $order->get_total(),
				'order_number'          => $order->id,
				'merchant_payment_code' => $order->id . '-' . md5(rand(123123, 9999999)),
				'items' => array_map(function($product) {
					return array(
						'name' => $product['name'],
						'unit_price' => $product['line_subtotal'],
					  	'quantity' => $product['qty'],
						'type' => $product['type']
					);
				}, $order->get_items()),
			)
		);

		if (!empty($this->configs->settings['due_date_days']) && in_array($this->api_name, array_keys(WC_EBANX_Constants::$CASH_PAYMENTS_TIMEZONES)))
		{
			$date = new DateTime();

			$date->setTimezone(new DateTimeZone(WC_EBANX_Constants::$CASH_PAYMENTS_TIMEZONES[$this->api_name]));
			$date->modify("+{$this->configs->settings['due_date_days']} day");

			$data['payment']['due_date'] = $date->format('d/m/Y');
		}

		if ($this->getTransactionAddress('country') === WC_EBANX_Constants::COUNTRY_BRAZIL) {
			$fields_options = array();
			if (isset($this->configs->settings['brazil_taxes_options']) && is_array($this->configs->settings['brazil_taxes_options'])) {
				$fields_options = $this->configs->settings['brazil_taxes_options'];
			}

			if (count($fields_options) === 1 && $fields_options[0] === 'cnpj') {
				$person_type = 'business';
			}
			if (in_array('cpf', $fields_options) && in_array('cnpj', $fields_options)) {
				$person_type = WC_EBANX_Request::read($this->names['ebanx_billing_brazil_person_type'], 'cpf') == 'cnpj' ? 'business' : 'personal';
			}


			$has_cpf = !empty(WC_EBANX_Request::read($this->names['ebanx_billing_brazil_document'], null));
			$has_cnpj = !empty(WC_EBANX_Request::read($this->names['ebanx_billing_brazil_cnpj'], null));

			if (
				empty(WC_EBANX_Request::read('billing_postcode', null)) ||
				empty(WC_EBANX_Request::read('billing_address_1', null)) ||
				empty(WC_EBANX_Request::read('billing_city', null)) ||
				empty(WC_EBANX_Request::read('billing_state', null)) ||
				($person_type == 'business' && (!$has_cnpj || empty(WC_EBANX_Request::read('billing_company', null)))) ||
				($person_type == 'personal' && !$has_cpf)
			) {
				throw new Exception('INVALID-FIELDS');
			}

			if ($person_type == 'business') {
				WC_EBANX_Request::set('ebanx_billing_document', WC_EBANX_Request::read($this->names['ebanx_billing_brazil_cnpj']));
			} else {
				WC_EBANX_Request::set('ebanx_billing_document', WC_EBANX_Request::read($this->names['ebanx_billing_brazil_document']));
				WC_EBANX_Request::set('ebanx_billing_birth_date', WC_EBANX_Request::read($this->names['ebanx_billing_brazil_birth_date']));
			}
		}

		if ($this->getTransactionAddress('country') === WC_EBANX_Constants::COUNTRY_CHILE) {
			if (empty(WC_EBANX_Request::read($this->names['ebanx_billing_chile_document'], null)) || empty(WC_EBANX_Request::read($this->names['ebanx_billing_chile_birth_date'], null))) {
				throw new Exception('INVALID-FIELDS');
			}

			WC_EBANX_Request::set('ebanx_billing_document', WC_EBANX_Request::read($this->names['ebanx_billing_chile_document']));
			WC_EBANX_Request::set('ebanx_billing_birth_date', WC_EBANX_Request::read($this->names['ebanx_billing_chile_birth_date']));
		}

		if ($this->getTransactionAddress('country') === WC_EBANX_Constants::COUNTRY_COLOMBIA) {
			WC_EBANX_Request::set('ebanx_billing_document', WC_EBANX_Request::read($this->names['ebanx_billing_colombia_document']));
		}

		$addresses = WC_EBANX_Request::read('billing_address_1');

		if (!empty(WC_EBANX_Request::read('billing_address_2', null))) {
			$addresses .= " - " . WC_EBANX_Request::read('billing_address_2');
		}

		$addresses = WC_EBANX_Helper::split_street($addresses);

		$street_number = empty($addresses['houseNumber']) ? 'S/N' : trim($addresses['houseNumber'] . ' ' . $addresses['additionToAddress']);
		$street_name = $addresses['streetName'];

		$newData = array();
		$newData['payment'] = array();

		$newData['payment']['person_type'] = $person_type;

		if (!empty(WC_EBANX_Request::read('ebanx_billing_document', null))) {
			$newData['payment']['document'] = WC_EBANX_Request::read('ebanx_billing_document');
		}

		if (!empty(WC_EBANX_Request::read('ebanx_billing_birth_date', null))) {
			$newData['payment']['birth_date'] = WC_EBANX_Request::read('ebanx_billing_birth_date');
		}

		if (!empty(WC_EBANX_Request::read('billing_postcode', null))) {
			$newData['payment']['zipcode'] = WC_EBANX_Request::read('billing_postcode');
		}

		if (!empty(WC_EBANX_Request::read('billing_address_1', null))) {
			$newData['payment']['address'] = $street_name;
		}

		if (!empty($street_number)) {
			$newData['payment']['street_number'] = $street_number;
		}

		if (!empty(WC_EBANX_Request::read('billing_city', null))) {
			$newData['payment']['city'] = WC_EBANX_Request::read('billing_city');
		}

		if (!empty(WC_EBANX_Request::read('billing_state', null))) {
			$newData['payment']['state'] = WC_EBANX_Request::read('billing_state');
		}

		if ($this->getTransactionAddress('country') === WC_EBANX_Constants::COUNTRY_BRAZIL) {

			if ($person_type == 'business') {
				$newData['payment']['responsible'] = array(
					"name" => $data['payment']['name']
				);
				$newData['payment']['name'] = WC_EBANX_Request::read('billing_company');
			}
		}

		$data['payment'] = array_merge($data['payment'], $newData['payment']);

		return $data;
	}

	/**
	 * Get the customer's address
	 *
	 * @param  string $attr
	 * @return boolean|string
	 */
	public function getTransactionAddress($attr = '')
	{
		if (
			!isset(WC()->customer)
			|| is_admin()
			|| empty(WC_EBANX_Request::read('billing_country', null))
			&& empty(WC()->customer->get_country())
		) {
			return false;
		}

		if (!empty(WC_EBANX_Request::read('billing_country', null))) {
			$this->address['country'] = trim(strtolower(WC_EBANX_Request::read('billing_country')));
		} else {
			$this->address['country'] = trim(strtolower(WC()->customer->get_country()));
		}

		if ($attr !== '' && !empty($this->address[$attr])) {
			return $this->address[$attr];
		}

		return $this->address;
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
		try {
			$order = wc_get_order($order_id);

			do_action('ebanx_before_process_payment', $order);

			if ($order->get_total() > 0) {
				$data = $this->request_data($order);

				$config = array(
					'integrationKey' => $this->private_key,
					'testMode'       => $this->is_sandbox_mode,
				);

				\Ebanx\Config::set($config);
				\Ebanx\Config::setDirectMode(true);

				$request = \Ebanx\EBANX::doRequest($data);

				$this->process_response($request, $order);
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

			$code = $e->getMessage();

			$languages = array(
				'mx' => 'es',
				'cl' => 'es',
				'pe' => 'es',
				'co' => 'es',
				'br' => 'pt-br',
			);
			$language = $languages[$country];

			$errors = WC_EBANX_Errors::get_errors();

			$message = !empty($errors[$language][$code]) ? $errors[$language][$code] : $errors[$language]['GENERAL'] . " ({$code})";

			WC()->session->set('refresh_totals', true);
			WC_EBANX::log("EBANX Error: $message");

			wc_add_notice($message, 'error');

			do_action('ebanx_process_payment_error', $message, $code);
			return;
		}
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param  WC_Order $order The order created
	 * @return void
	 */
	public static function thankyou_page($data)
	{
		$file_name = "{$data['method']}/payment-{$data['order_status']}.php";

		if (file_exists(WC_EBANX::get_templates_path() . $file_name)) {
			wc_get_template(
				$file_name,
				$data['data'],
				'woocommerce/ebanx/',
				WC_EBANX::get_templates_path()
			);
		}
	}

	/**
	 * Clean the cart and dispatch the data to request
	 *
	 * @param  array $data  The checkout's data
	 * @return array
	 */
	protected function dispatch($data)
	{
		WC()->cart->empty_cart();

		return $data;
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
		// To save only on DB to internal use
		update_post_meta($order->id, '_ebanx_payment_hash', $request->payment->hash);
		update_post_meta($order->id, '_ebanx_payment_open_date', $request->payment->open_date);

		if (WC_EBANX_Request::has('billing_email')) {
			update_post_meta($order->id, '_ebanx_payment_customer_email', sanitize_email(WC_EBANX_Request::read('billing_email')));
		}

		if (WC_EBANX_Request::has('billing_phone')) {
			update_post_meta($order->id, '_ebanx_payment_customer_phone', sanitize_text_field(WC_EBANX_Request::read('billing_phone')));
		}

		if (WC_EBANX_Request::has('billing_address_1')) {
			update_post_meta($order->id, '_ebanx_payment_customer_address', sanitize_text_field(WC_EBANX_Request::read('billing_address_1')));
		}
	}

	/**
	 * Save user's meta fields for future use
	 *
	 * @param  WC_Order $order The order created
	 * @return void
	 */
	protected function save_user_meta_fields($order)
	{
		if ($this->userId) {
			if (trim(strtolower($order->billing_country)) === WC_EBANX_Constants::COUNTRY_BRAZIL) {
				if (WC_EBANX_Request::has($this->names['ebanx_billing_brazil_document'])) {
					update_user_meta($this->userId, '_ebanx_billing_brazil_document', sanitize_text_field(WC_EBANX_Request::read($this->names['ebanx_billing_brazil_document'])));
				}

				if (WC_EBANX_Request::has($this->names['ebanx_billing_brazil_birth_date'])) {
					update_user_meta($this->userId, '_ebanx_billing_brazil_birth_date', sanitize_text_field(WC_EBANX_Request::read($this->names['ebanx_billing_brazil_birth_date'])));
				}

				if (WC_EBANX_Request::has($this->names['ebanx_billing_brazil_cnpj'])) {
					update_user_meta($this->userId, '_ebanx_billing_brazil_cnpj', sanitize_text_field(WC_EBANX_Request::read($this->names['ebanx_billing_brazil_cnpj'])));
				}

				if (WC_EBANX_Request::has($this->names['ebanx_billing_brazil_person_type'])) {
					update_user_meta($this->userId, '_ebanx_billing_brazil_person_type', sanitize_text_field(WC_EBANX_Request::read($this->names['ebanx_billing_brazil_person_type'])));
				}
			}

			if (trim(strtolower($order->billing_country)) === WC_EBANX_Constants::COUNTRY_CHILE) {
				if (WC_EBANX_Request::has('ebanx_billing_chile_document')) {
					update_user_meta($this->userId, '_ebanx_billing_chile_document', sanitize_text_field(WC_EBANX_Request::read('ebanx_billing_chile_document')));
				}

				if (WC_EBANX_Request::has('ebanx_billing_chile_birth_date')) {
					update_user_meta($this->userId, '_ebanx_billing_chile_birth_date', sanitize_text_field(WC_EBANX_Request::read('ebanx_billing_chile_birth_date')));
				}
			}

			if ($this->getTransactionAddress('country') === WC_EBANX_Constants::COUNTRY_COLOMBIA) {
				if (WC_EBANX_Request::has('ebanx_billing_colombia_document')) {
					update_user_meta($this->userId, '_ebanx_billing_colombia_document', sanitize_text_field(WC_EBANX_Request::read('ebanx_billing_colombia_document')));
				}
			}
		}
	}

	/**
	 * It just process the errors response and it generates a log to the merchant on Wordpress panel
	 * It always throws an Exception
	 *
	 * @param  Object $request      The EBANX error object
	 * @param  WC_Order $order      The WooCommerce order
	 * @return void
	 */
	protected function process_response_error($request, $order)
	{
		$code = isset($request->status_code) ? $request->status_code : 'GENERAL';
		$error_message = __(sprintf('EBANX: An error occurred: %s - %s', $code, $request->status_message), 'woocommerce-gateway-ebanx');

		$order->update_status('failed', $error_message);
		$order->add_order_note($error_message);

		do_action('ebanx_process_response_error', $order, $code);

		throw new Exception($code);
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
		WC_EBANX::log(sprintf(__('Processing response: %s', 'woocommerce-gateway-ebanx'), print_r($request, true)));

		if ($request->status == 'ERROR') {
			return $this->process_response_error($request, $order);
		}

		if (
			isset($request->payment->transaction_status)
			&& $request->payment->transaction_status->code === 'NOK'
			&& $request->payment->transaction_status->acquirer === 'EBANX'
			&& $this->is_sandbox_mode
		) {
			throw new Exception('SANDBOX-INVALID-CC-NUMBER');
		}

		$message = __(sprintf('Payment approved. Hash: %s', $request->payment->hash), 'woocommerce-gateway-ebanx');

		WC_EBANX::log($message);

		if ($request->payment->status == 'CA') {
			$order->add_order_note(__('EBANX: The payment has failed.', 'woocommerce-gateway-ebanx'));
			$order->update_status('failed');
		}

		if ($request->payment->status == 'OP') {
			$order->add_order_note(__('EBANX: The payment was opened.', 'woocommerce-gateway-ebanx'));
			$order->update_status('pending');
		}

		if ($request->payment->status == 'PE') {
			$order->add_order_note(__('EBANX: The order is awaiting payment.', 'woocommerce-gateway-ebanx'));
			$order->update_status('on-hold');
		}

		if ($request->payment->pre_approved && $request->payment->status == 'CO') {
			$order->add_order_note(__('EBANX: The transaction was paid.', 'woocommerce-gateway-ebanx'));
			$order->payment_complete($request->payment->hash);
			$order->update_status('processing');
		}

		// Save post's meta fields
		$this->save_order_meta_fields($order, $request);

		// Save user's fields
		$this->save_user_meta_fields($order);

		do_action('ebanx_process_response', $order);
	}

	/**
	 * Create the hooks to process cash payments
	 *
	 * @param  array  $codes
	 * @param  string $notificationType     The type of the description
	 * @return void
	 */
	final public function process_hook(array $codes, $notificationType)
	{
		do_action('ebanx_before_process_hook', $codes, $notificationType);

		$config = array(
			'integrationKey' => $this->private_key,
			'testMode'       => $this->is_sandbox_mode,
		);

		\Ebanx\Config::set($config);

		/**
		 * Validates the request parameters
		 */
		if (isset($codes['hash']) && !empty($codes['hash']) && isset($codes['merchant_payment_code']) && !empty($codes['merchant_payment_code'])) {
			unset($codes['merchant_payment_code']);
		}

		$data = \Ebanx\EBANX::doQuery($codes);

		$order_id = WC_EBANX_Helper::get_post_id_by_meta_key_and_value('_ebanx_payment_hash', $data->payment->hash);

		$order = new WC_Order($order_id);

		switch (strtoupper($notificationType)) {
			case 'REFUND':
				$this->process_refund_hook($order, $data);

				break;
			case 'UPDATE':
				$this->update_payment($order, $data);

				break;
		};

		do_action('ebanx_after_process_hook', $order, $notificationType);

		return $order;
	}

	/**
	 * Updates the payment when receive a notification from EBANX
	 *
	 * @param WC_Order $order
	 * @param EBANX_Request $data
	 * @return void
	 */
	final public function update_payment($order, $data) {
		$requestStatus = strtoupper($data->payment->status);

		$status = array(
			'CO' => 'Confirmed',
			'CA' => 'Canceled',
			'PE' => 'Pending',
			'OP' => 'Opened'
		);
		$new_status = null;
		switch ($requestStatus) {
			case 'CO':
				$new_status = 'processing';
				break;
			case 'CA':
				$new_status = 'failed';
				break;
			case 'PE':
				$new_status = 'on-hold';
				break;
			case 'OP':
				$new_status = 'pending';
				break;
		}

		if ($new_status !== $order->status) {
			$paymentStatus = $status[$data->payment->status];
			$order->add_order_note(sprintf(__('EBANX: The payment has been updated to: %s.', 'woocommerce-gateway-ebanx'), $paymentStatus));
			$order->update_status($new_status);
		}
	}

	/**
	 * Updates the refunds when receivers a EBANX refund notification
	 *
	 * @param WC_Order $order
	 * @param EBANX_Request $data
	 * @return void
	 */
	final public function process_refund_hook($order, $data) {
		$refunds = current(get_post_meta($order->id, "_ebanx_payment_refunds"));

		foreach ($refunds as $k => $ref) {
			foreach ($data->payment->refunds as $refund) {
				if ($ref->id == $refund->id) {
					if ($refund->status == 'CO' && $refunds[$k]->status != 'CO') {
						$order->add_order_note(sprintf(__('EBANX: Your Refund was confirmed to EBANX - Refund ID: %s', 'woocommerce-gateway-ebanx'), $refund->id));
					}
					if ($refund->status == 'CA' && $refunds[$k]->status != 'CA') {
						$order->add_order_note(sprintf(__('EBANX: Your Refund was canceled to EBANX - Refund ID: %s', 'woocommerce-gateway-ebanx'), $refund->id));
					}

					$refunds[$k]->status       = $refund->status; // status == co save note
					$refunds[$k]->cancel_date  = $refund->cancel_date;
					$refunds[$k]->request_date = $refund->request_date;
					$refunds[$k]->pending_date = $refund->pending_date;
					$refunds[$k]->confirm_date = $refund->confirm_date;
				}
			}
		}

		update_post_meta($order->id, "_ebanx_payment_refunds", $refunds);
	}

	/**
	 * Create the converter amount on checkout page
	 *
	 * @param  string $currency Possible currencies: BRL, USD, EUR, PEN, CLP, COP, MXN
	 * @return void
	 */
	public function checkout_rate_conversion($currency, $template = true, $country = null, $instalments = null) {
		if ( ! in_array($this->merchant_currency, WC_EBANX_Constants::$CURRENCIES_CODES_ALLOWED )
			|| $this->configs->get_setting_or_default('show_local_amount', 'yes') !== 'yes') {
			return;
		}

		$amount = WC()->cart->total;

		if ($country === null) {
			$country = $this->getTransactionAddress('country');
		}

		if ( in_array($this->merchant_currency, array(
			WC_EBANX_Constants::CURRENCY_CODE_USD,
			WC_EBANX_Constants::CURRENCY_CODE_EUR) ) ) {
			$rate = round(floatval($this->get_local_currency_rate_for_site($currency)), 2);

			if ( WC()->cart->prices_include_tax ) {
				$amount = WC()->cart->total + WC()->cart->tax_total;
			}

			$amount *= $rate;
		}

		// Applies instalments taxes
		if ( $this->get_setting_or_default('interest_rates_enabled', 'no') === 'yes'
			&& $instalments !== null ) {
			$interest_rate = floatval($this->configs->settings['interest_rates_' . sprintf("%02d", $instalments)]);

			$amount += ($amount * $interest_rate / 100);
		}

		// Applies IOF for Brazil payments only
		if ( $country === WC_EBANX_Constants::COUNTRY_BRAZIL ) {
			$amount += ($amount * WC_EBANX_Constants::BRAZIL_TAX);
		}

		if ($instalments !== null) {
			$instalment_price = $amount / $instalments;
			$instalment_price = round(floatval($instalment_price), 2);
			$amount = $instalment_price * $instalments;
		}

		$message = $this->get_checkout_message($amount, $currency, $country);

		if ($template) {
			wc_get_template(
				'checkout-conversion-rate.php',
				array(
					'message' => $message
				),
				'woocommerce/ebanx/',
				WC_EBANX::get_templates_path()
			);
		}

		return $message;
	}

	/**
	 * Generates the checkout message
	 *
	 * @param int $amount The total price of the order
	 * @param  string $currency Possible currencies: BRL, USD, EUR, PEN, CLP, COP, MXN
	 * @param string $country The country code
	 * @return void
	 */
	public function get_checkout_message($amount, $currency, $country) {
		$price = wc_price($amount, array('currency' => $currency));

		$languages = array(
			'mx' => 'es',
			'cl' => 'es',
			'pe' => 'es',
			'co' => 'es',
			'br' => 'pt-br',
		);
		$language = $languages[$country];

		$texts = array(
			'pt-br' => array(
				'INTRO'                                      => 'Total a pagar ',
				WC_EBANX_Constants::CURRENCY_CODE_BRL    => 'com IOF (0.38%)'
			),
			'es'    => array(
				'INTRO'                                      => 'Total a pagar en ',
				WC_EBANX_Constants::CURRENCY_CODE_MXN    => 'Peso mexicano',
				WC_EBANX_Constants::CURRENCY_CODE_CLP    => 'Peso chileno',
				WC_EBANX_Constants::CURRENCY_CODE_PEN    => 'Sol peruano',
				WC_EBANX_Constants::CURRENCY_CODE_COP    => 'Peso colombiano',
				WC_EBANX_Constants::CURRENCY_CODE_BRL    => 'Real brasileño'
			),
		);

		$message = $texts[$language]['INTRO'];
		$message .= !empty($texts[$language][$currency]) ? $texts[$language][$currency] : $currency;
		$message .= ': <strong class="ebanx-amount-total">' . $price . '</strong>';

		return $message;
	}
}
