<?php
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-constants.php';
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-helper.php';
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-constants.php';


use Ebanx\Benjamin\Models\Address;
use Ebanx\Benjamin\Models\Payment;
use Ebanx\Benjamin\Models\Person;

class EBANX_Payment_Adapter
{
	public static function transform($order, $configs, $api_name, $payload)
	{
		return new Payment([
			'amountTotal' => $order->get_total(),
			'orderNumber' => $order->id,
			'dueDate' => static::transform_due_date($configs, $api_name),
			'address' => static::transform_address($order, $payload),
			'person' => static::transform_person($order, $configs, $payload),
			'merchantPaymentCode' => substr($order->id . '-' . md5(rand(123123, 9999999)), 0, 40),
		]);
	}

	private static function transform_due_date($configs, $api_name)
	{
		$date = '';
		if (!empty($configs->settings['due_date_days']) && in_array($api_name, array_keys(WC_EBANX_Constants::$CASH_PAYMENTS_TIMEZONES)))
		{
			$date = new DateTime();

			$date->setTimezone(new DateTimeZone(WC_EBANX_Constants::$CASH_PAYMENTS_TIMEZONES[$api_name]));
			$date->modify("+{$configs->settings['due_date_days']} day");

			$date = $date->format('d/m/Y');
		}

		return $date;
	}

	private static function transform_address($order, $payload)
	{
		$addresses = $payload['billing_address_1'];

		if (!empty($payload['billing_address_2'])) {
			$addresses .= " - " . $payload['billing_address_2'];
		}

		$addresses = WC_EBANX_Helper::split_street($addresses);
		$street_number = empty($addresses['houseNumber']) ? 'S/N' : trim($addresses['houseNumber'] . ' ' . $addresses['additionToAddress']);

		return new Address([
			'address' => $addresses['streetName'],
			'streetNumber' => $street_number,
			'city' => $payload['billing_city'],
			'country' => $order->billing_country,
			'state' => array_key_exists('billing_state', $payload) ? $payload['billing_state'] : '',
			'zipcode' => $payload['billing_postcode'],
		]);
	}

	private static function transform_person($order, $configs, $payload)
	{
		return new Person([
			'document' => static::get_document($configs, $payload),
			'email' => $order->billing_email,
			'ip' => WC_Geolocation::get_ip_address(),
			'name' => $order->billing_first_name . ' ' . $order->billing_last_name,
			'phoneNumber' => $order->billing_phone,
		]);
	}

	private static function get_document($configs, $payload) {
		$country = trim(strtolower(WC()->customer->get_country()));

		switch ($country) {
			case WC_EBANX_Constants::COUNTRY_BRAZIL:
				return static::get_brazilian_document($configs, $payload);
		}
	}

	/**
	 * @param $configs
	 * @param $payload
	 *
	 * @return string
	 * @throws Exception
	 */
	private static function get_brazilian_document($configs, $payload) {
		$fields_options = array();
		$person_type = 'personal';

		if (isset($configs->settings['brazil_taxes_options']) && is_array($configs->settings['brazil_taxes_options'])) {
			$fields_options = $configs->settings['brazil_taxes_options'];
		}

		if (count($fields_options) === 1 && $fields_options[0] === 'cnpj') {
			$person_type = 'business';
		}

		if (in_array('cpf', $fields_options) && in_array('cnpj', $fields_options)) {
			$person_type = $payload['ebanx_billing_brazil_person_type'] == 'cnpj' ? 'business' : 'personal';
		}

		$has_cpf = !empty($payload['ebanx_billing_brazil_document']);
		$has_cnpj = !empty($payload['ebanx_billing_brazil_cnpj']);

		if (
			empty($payload['billing_postcode']) ||
			empty($payload['billing_address_1']) ||
			empty($payload['billing_city']) ||
			empty($payload['billing_state']) ||
			($person_type === 'business' && (!$has_cnpj || empty($payload['billing_company']))) ||
			($person_type === 'personal' && !$has_cpf)
		) {
			throw new Exception('INVALID-FIELDS');
		}

		if ($person_type === 'business') {
			return $payload['ebanx_billing_brazil_cnpj'];
		}
		return $payload['ebanx_billing_brazil_document'];
	}
}
