<?php

use Ebanx\Benjamin\Models\Address;
use Ebanx\Benjamin\Models\Card;
use Ebanx\Benjamin\Models\Country;
use Ebanx\Benjamin\Models\Item;
use Ebanx\Benjamin\Models\Payment;
use Ebanx\Benjamin\Models\Person;

/**
 * Class WC_EBANX_Payment_Adapter
 */
class WC_EBANX_Payment_Adapter {
	/**
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 *
	 * @return Payment
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function transform( $order, $configs, $names ) {
		return new Payment([
			'amountTotal' => $order->get_total(),
			'orderNumber' => $order->id,
			'dueDate' => static::transform_due_date( $configs ),
			'address' => static::transform_address( $order ),
			'person' => static::transform_person( $order, $configs, $names ),
			'responsible' => static::transform_person( $order, $configs, $names ),
			'items' => static::transform_items( $order ),
			'merchantPaymentCode' => substr( $order->id . '-' . md5( rand( 123123, 9999999 ) ), 0, 40 ),
		]);
	}

	/**
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 *
	 * @return Payment
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function transform_card( $order, $configs, $names ) {
		$payment = self::transform( $order, $configs, $names );
		$country = trim( strtolower( WC()->customer->get_country() ) );

		if ( in_array( $country, WC_EBANX_Constants::$credit_card_countries ) ) {
			$payment->instalments = '1';

			if ( $configs->settings['credit_card_instalments'] > 1 && WC_EBANX_Request::has( 'ebanx_billing_instalments' ) ) {
				$payment->instalments = WC_EBANX_Request::read( 'ebanx_billing_instalments' );
			}
		}

		if ( ! empty( WC_EBANX_Request::read( 'ebanx_device_fingerprint', null ) ) ) {
			$payment->device_id = WC_EBANX_Request::read( 'ebanx_device_fingerprint' );
		}

		$token = WC_EBANX_Request::has( 'ebanx_debit_token' )
			? WC_EBANX_Request::read( 'ebanx_debit_token' )
			: WC_EBANX_Request::read( 'ebanx_token' );

		$brand = WC_EBANX_Request::has( 'ebanx_brand' ) ? WC_EBANX_Request::read( 'ebanx_brand' ) : '';

		$payment->card = new Card([
			'autoCapture' => ( 'yes' === $configs->settings['capture_enabled'] ),
			'token' => $token,
			'cvv' => WC_EBANX_Request::read( 'ebanx_billing_cvv' ),
			'type' => $brand,
		]);

		return $payment;
	}

	/**
	 * @param WC_EBANX_Global_Gateway $configs
	 *
	 * @return DateTime|string
	 */
	private static function transform_due_date( $configs ) {
		$due_date = '';
		if ( ! empty( $configs->settings['due_date_days'] ) ) {
			$due_date = new DateTime();
			$due_date->modify( "+{$configs->settings['due_date_days']} day" );
		}

		return $due_date;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return Address
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function transform_address( $order ) {
		$addresses = WC_EBANX_Request::read( 'billing_address_1', null );

		if ( ! empty( WC_EBANX_Request::read( 'billing_address_2', null ) ) ) {
			$addresses .= ' - ' . WC_EBANX_Request::read( 'billing_address_2', null );
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
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 *
	 * @return Person
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function transform_person( $order, $configs, $names ) {
		$document = static::get_document( $configs, $names, $order );

		return new Person([
			'type' => static::get_person_type( $configs, $names ),
			'document' => $document,
			'email' => $order->billing_email,
			'ip' => WC_Geolocation::get_ip_address(),
			'name' => $order->billing_first_name . ' ' . $order->billing_last_name,
			'phoneNumber' => $order->billing_phone,
		]);
	}

	/**
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 * @param WC_Order                $order
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function get_document( $configs, $names, $order ) {
		$country = trim( strtolower( WC()->customer->get_country() ) );

		switch ( $country ) {
			case WC_EBANX_Constants::COUNTRY_ARGENTINA:
				return static::get_argentinian_document( $names );
				break;
			case WC_EBANX_Constants::COUNTRY_BRAZIL:
				return static::get_brazilian_document( $configs, $names );
				break;
			case WC_EBANX_Constants::COUNTRY_CHILE:
				return static::get_chilean_document( $order, $names );
				break;
			case WC_EBANX_Constants::COUNTRY_COLOMBIA:
				return static::get_colombian_document( $order, $names );
				break;
			case WC_EBANX_Constants::COUNTRY_PERU:
				return static::get_peruvian_document( $names );
				break;
			default:
				return '';
		}
	}

	/**
	 * @param array $names
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function get_argentinian_document( $names ) {
		$document = WC_EBANX_Request::read( $names['ebanx_billing_argentina_document'], null );
		if ( null === $document ) {
			throw new Exception( 'BP-DR-22' );
		}

		return $document;
	}

	/**
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function get_brazilian_document( $configs, $names ) {
		$cpf = WC_EBANX_Request::read( $names['ebanx_billing_brazil_document'], null );
		$cnpj = WC_EBANX_Request::read( $names['ebanx_billing_brazil_cnpj'], null );

		$person_type = static::get_person_type( $configs, $names );

		$has_cpf  = ! empty( $cpf );
		$has_cnpj = ! empty( $cnpj );

		if (
			empty( WC_EBANX_Request::read( 'billing_postcode', null ) ) ||
			empty( WC_EBANX_Request::read( 'billing_address_1', null ) ) ||
			empty( WC_EBANX_Request::read( 'billing_city', null ) ) ||
			empty( WC_EBANX_Request::read( 'billing_state', null ) ) ||
			( Person::TYPE_BUSINESS === $person_type && ( ! $has_cnpj || empty( WC_EBANX_Request::read( 'billing_company', null ) ) ) ) ||
			( Person::TYPE_PERSONAL === $person_type && ! $has_cpf )
		) {
			throw new Exception( 'INVALID-FIELDS' );
		}

		if ( Person::TYPE_BUSINESS === $person_type ) {
			return $cnpj;
		}

		return $cpf;
	}

	/**
	 * @param WC_Order $order
	 * @param array    $names
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function get_chilean_document( $order, $names ) {
		$document = WC_EBANX_Request::read( $names['ebanx_billing_chile_document'], null );
		if ( null === $document && 'ebanx-webpay' === $order->get_payment_method() ) {
			throw new Exception( 'BP-DR-22' );
		}

		return $document;
	}

	/**
	 * @param WC_Order $order
	 * @param array    $names
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function get_colombian_document( $order, $names ) {
		$document = WC_EBANX_Request::read( $names['ebanx_billing_colombia_document'], null );
		if ( null === $document && 'ebanx-credit-card-co' === $order->get_payment_method() ) {
			throw new Exception( 'BP-DR-22' );
		}

		return $document;
	}

	/**
	 * @param array $names
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function get_peruvian_document( $names ) {
		$document = WC_EBANX_Request::read( $names['ebanx_billing_peru_document'], null );
		if ( null === $document ) {
			throw new Exception( 'BP-DR-22' );
		}

		return $document;
	}

	/**
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function get_person_type( $configs, $names ) {
		$fields_options = array();
		$person_type = Person::TYPE_PERSONAL;

		if ( isset( $configs->settings['brazil_taxes_options'] ) && is_array( $configs->settings['brazil_taxes_options'] ) ) {
			$fields_options = $configs->settings['brazil_taxes_options'];
		}

		if ( count( $fields_options ) === 1 && 'cnpj' === $fields_options[0] ) {
			$person_type = Person::TYPE_BUSINESS;
		}

		if ( in_array( 'cpf', $fields_options ) && in_array( 'cnpj', $fields_options ) ) {
			$person_type = 'cnpj' === WC_EBANX_Request::read( $names['ebanx_billing_brazil_person_type'], 'cpf' ) ? Person::TYPE_BUSINESS : Person::TYPE_PERSONAL;
		}

		return $person_type;
	}

	/**
	 * @param $order WC_Order $order
	 *
	 * @return array
	 */
	private static function transform_items( $order ) {
		return array_map( function( $product ) {
			return new Item([
				'name' => $product['name'],
				'unitPrice' => $product['line_subtotal'],
				'quantity' => $product['qty'],
				'type' => $product['type'],
			]);
		}, $order->get_items() );
	}
}
