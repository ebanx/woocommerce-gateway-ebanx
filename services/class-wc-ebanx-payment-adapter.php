<?php
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-constants.php';
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-helper.php';
require_once WC_EBANX_SERVICES_DIR . 'class-wc-ebanx-request.php';


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
			'person' => static::transform_person($order),
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

	private static function transform_person($order)
	{
		return new Person([
			'email' => $order->billing_email,
			'ip' => WC_Geolocation::get_ip_address(),
			'name' => $order->billing_first_name . ' ' . $order->billing_last_name,
			'phoneNumber' => $order->billing_phone,
		]);
	}
}
