<?php
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-constants.php';
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-helper.php';
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-request.php';


use Ebanx\Benjamin\Models\Address;
use Ebanx\Benjamin\Models\Card;
use Ebanx\Benjamin\Models\Country;
use Ebanx\Benjamin\Models\Item;
use Ebanx\Benjamin\Models\Payment;
use Ebanx\Benjamin\Models\Person;

class WC_EBANX_Payment_Adapter
{
	/**
	 * @param $order WC_Order
	 * @param $configs WC_EBANX_Global_Gateway
	 * @param $api_name string
	 * @param $names array
	 *
	 * @return Payment
	 * @throws Exception
	 */
	public static function transform($order, $configs, $api_name, $names) {
		return new Payment([
			'amountTotal' => $order->get_total(),
			'orderNumber' => $order->id,
			'dueDate' => static::transform_due_date($configs, $api_name),
			'address' => static::transform_address($order),
			'person' => static::transform_person($order, $configs, $names),
			'responsible' => static::transform_person($order, $configs, $names),
			'items' => static::transform_items( $order ),
			'merchantPaymentCode' => substr($order->id . '-' . md5(rand(123123, 9999999)), 0, 40),
		]);
	}

	/**
	 * @param $order WC_Order
	 * @param $configs WC_EBANX_Global_Gateway
	 * @param $api_name string
	 * @param $names array
	 *
	 * @return Payment
	 * @throws Exception
	 */
	public static function transform_card( $order, $configs, $api_name, $names ) {
		$payment = self::transform( $order, $configs, $api_name, $names );
		$country = trim(strtolower(WC()->customer->get_country()));

		if (in_array($country, WC_EBANX_Constants::$CREDIT_CARD_COUNTRIES)) {
			$payment->instalments = '1';

			if ($configs->settings['credit_card_instalments'] > 1 && WC_EBANX_Request::has('ebanx_billing_instalments')) {
				$payment->instalments = WC_EBANX_Request::read('ebanx_billing_instalments');
			}
		}

		if (!empty(WC_EBANX_Request::read('ebanx_device_fingerprint', null))) {
			$payment->device_id = WC_EBANX_Request::read('ebanx_device_fingerprint');
		}

		$payment->card = new Card([
			'autoCapture' => ($configs->settings['capture_enabled'] === 'yes'),
			'token' => WC_EBANX_Request::read('ebanx_token'),
			'cvv' => WC_EBANX_Request::read('ebanx_billing_cvv'),
			'type' => WC_EBANX_Request::read('ebanx_brand'),
		]);

		return $payment;
	}

	/**
	 * @param $configs
	 * @param $api_name
	 *
	 * @return DateTime|string
	 */
	private static function transform_due_date($configs, $api_name) {
		$date = '';
		if (!empty($configs->settings['due_date_days']) && in_array($api_name, array_keys(WC_EBANX_Constants::$CASH_PAYMENTS_TIMEZONES)))
		{
			$date = new DateTime();
			$date->modify("+{$configs->settings['due_date_days']} day");
		}

		return $date;
	}

	/**
	 * @param $order WC_Order
	 *
	 * @return Address
	 * @throws Exception
	 */
	private static function transform_address( $order ) {
		$addresses = WC_EBANX_Request::read( 'billing_address_1', null );

		if ( ! empty(WC_EBANX_Request::read( 'billing_address_2', null ) ) ) {
			$addresses .= " - " . WC_EBANX_Request::read( 'billing_address_2', null );
		}

		$addresses = WC_EBANX_Helper::split_street( $addresses );
		$street_number = empty( $addresses['houseNumber'] ) ? 'S/N' : trim( $addresses['houseNumber'] . ' ' . $addresses['additionToAddress'] );

		return new Address( [
			'address' => $addresses['streetName'],
			'streetNumber' => $street_number,
			'city' => WC_EBANX_Request::read( 'billing_city', null ),
			'country' => Country::fromIso( $order->billing_country ),
			'state' => WC_EBANX_Request::read( 'billing_state', null ),
			'zipcode' => WC_EBANX_Request::read( 'billing_postcode', null ),
		] );
	}

	/**
	 * @param $order WC_Order
	 * @param $configs WC_EBANX_Global_Gateway
	 * @param $names array
	 *
	 * @return Person
	 * @throws Exception
	 */
	private static function transform_person( $order, $configs, $names ) {
		$documentInfo = static::get_document( $configs, $names );

		return new Person([
			'type' => $documentInfo['type'],
			'document' => $documentInfo['number'],
			'email' => $order->billing_email,
			'ip' => WC_Geolocation::get_ip_address(),
			'name' => $order->billing_first_name . ' ' . $order->billing_last_name,
			'phoneNumber' => $order->billing_phone,
		]);
	}

	/**
	 * @param $configs WC_EBANX_Global_Gateway
	 * @param $names array
	 *
	 * @return array
	 * @throws Exception
	 */
	private static function get_document($configs, $names) {
		$country = trim(strtolower(WC()->customer->get_country()));

		switch ($country) {
			case WC_EBANX_Constants::COUNTRY_BRAZIL:
				return static::get_brazilian_document($configs, $names);
		}
	}

	/**
	 * @param $configs WC_EBANX_Global_Gateway
	 * @param $names array
	 *
	 * @return array
	 * @throws Exception
	 */
	private static function get_brazilian_document($configs, $names) {
		$cpf = WC_EBANX_Request::read( $names['ebanx_billing_brazil_document'], null );
		$cnpj = WC_EBANX_Request::read( $names['ebanx_billing_brazil_cnpj'], null );

		$fields_options = array();
		$person_type = Person::TYPE_PERSONAL;

		if (isset($configs->settings['brazil_taxes_options']) && is_array($configs->settings['brazil_taxes_options'])) {
			$fields_options = $configs->settings['brazil_taxes_options'];
		}

		if (count($fields_options) === 1 && $fields_options[0] === 'cnpj') {
			$person_type = Person::TYPE_BUSINESS;
		}

		if (in_array('cpf', $fields_options) && in_array('cnpj', $fields_options)) {
			$person_type = WC_EBANX_Request::read( $names['ebanx_billing_brazil_person_type'], 'cpf' ) === 'cnpj' ? Person::TYPE_BUSINESS : Person::TYPE_PERSONAL;
		}

		$has_cpf  = ! empty( $cpf );
		$has_cnpj = ! empty( $cnpj );

		if (
			empty( WC_EBANX_Request::read( 'billing_postcode', null ) ) ||
			empty( WC_EBANX_Request::read( 'billing_address_1', null ) ) ||
			empty( WC_EBANX_Request::read( 'billing_city', null ) ) ||
			empty( WC_EBANX_Request::read( 'billing_state', null ) ) ||
			( $person_type === Person::TYPE_BUSINESS && ( ! $has_cnpj || empty( WC_EBANX_Request::read( 'billing_company', null ) ) ) ) ||
			( $person_type === Person::TYPE_PERSONAL && ! $has_cpf )
		) {
			throw new Exception('INVALID-FIELDS');
		}

		if ($person_type === Person::TYPE_BUSINESS) {
			return $cnpj;
		}

		return [
			'number' => $cpf,
			'type' => $person_type
		];
	}

	/**
	 * @param $order WC_Order
	 *
	 * @return array
	 */
	private static function transform_items( $order ) {
		return array_map(function($product) {
			return new Item([
				'name' => $product['name'],
				'unit_price' => $product['line_subtotal'],
				'quantity' => $product['qty'],
				'type' => $product['type'],
			]);
		}, $order->get_items());
	}
}
