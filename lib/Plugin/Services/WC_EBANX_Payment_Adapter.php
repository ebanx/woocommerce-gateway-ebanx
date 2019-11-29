<?php

namespace EBANX\Plugin\Services;

use Ebanx\Benjamin\Models\Address;
use Ebanx\Benjamin\Models\Card;
use Ebanx\Benjamin\Models\Country;
use Ebanx\Benjamin\Models\Item;
use Ebanx\Benjamin\Models\Payment;
use Ebanx\Benjamin\Models\Person;
use Exception;

/**
 * Class WC_EBANX_Payment_Adapter
 */
class WC_EBANX_Payment_Adapter {
	/**
	 *
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 * @param string                  $gateway_id
	 *
	 * @return Payment
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function transform( $order, $configs, $names, $gateway_id ) {
		return new Payment(
			[
				'amountTotal'         => $order->get_total(),
				'orderNumber'         => $order->get_id(),
				'dueDate'             => static::transform_due_date( $configs ),
				'address'             => static::transform_address( $order, $gateway_id ),
				'person'              => static::transform_person( $order, $configs, $names, $gateway_id ),
				'responsible'         => static::transform_person( $order, $configs, $names, $gateway_id ),
				'items'               => static::transform_items( $order ),
				'merchantPaymentCode' => substr( $order->get_id() . '-' . md5( rand( 123123, 9999999 ) ), 0, 40 ),
				'riskProfileId'       => 'Wx' . str_replace( '.', 'x', \WC_EBANX::get_plugin_version() ),
			]
		);
	}

	/**
	 *
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 * @param string                  $gateway_id
	 *
	 * @return Payment
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function transform_card( $order, $configs, $names, $gateway_id ) {
		$payment = self::transform( $order, $configs, $names, $gateway_id );
		$country = trim( strtolower( WC()->customer->get_billing_country() ) );

		if ( in_array( $country, WC_EBANX_Constants::$credit_card_countries ) ) {
			$payment->instalments = '1';

			if ( $configs->settings[ "{$country}_credit_card_instalments" ] > 1 && \WC_EBANX_Request::has( 'ebanx_billing_instalments' ) ) {
				$payment->instalments = \WC_EBANX_Request::read( 'ebanx_billing_instalments' );
			}
		}

		if ( \WC_EBANX_Request::has( 'ebanx_device_fingerprint' ) ) {
			$payment->deviceId = \WC_EBANX_Request::read( 'ebanx_device_fingerprint' );
		}

		$token = \WC_EBANX_Request::has( 'ebanx_debit_token' )
			? \WC_EBANX_Request::read( 'ebanx_debit_token' )
			: \WC_EBANX_Request::read( 'ebanx_token' );

		$brand = \WC_EBANX_Request::has( 'ebanx_brand' ) ? \WC_EBANX_Request::read( 'ebanx_brand' ) : '';

		$payment->card = new Card(
			[
				'autoCapture' => ( 'yes' === $configs->settings['capture_enabled'] ),
				'token'       => $token,
				'cvv'         => \WC_EBANX_Request::read( 'ebanx_billing_cvv' ),
				'type'        => $brand,
			]
		);

		$payment->manualReview = 'yes' === $configs->settings['manual_review_enabled']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		return $payment;
	}

	/**
	 *
	 * @param WC_EBANX_Global_Gateway $configs
	 *
	 * @return DateTime|string
	 */
	private static function transform_due_date( $configs ) {
		$due_date = '';
		if ( ! empty( $configs->settings['due_date_days'] ) ) {
			$due_date = new \DateTime();
			$due_date->modify( "+{$configs->settings['due_date_days']} day" );
		}

		return $due_date;
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @param string   $gateway_id
	 *
	 * @return Address
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function transform_address( $order, $gateway_id ) {

		if (
			( empty( \WC_EBANX_Request::read( 'billing_postcode', null ) )
				&& empty( \WC_EBANX_Request::read( $gateway_id, null )['billing_postcode'] )
				&& $order->get_billing_country() !== 'BO'
			)
			|| ( empty( \WC_EBANX_Request::read( 'billing_address_1', null ) )
				&& empty( \WC_EBANX_Request::read( $gateway_id, null )['billing_address_1'] ) )
			|| ( empty( \WC_EBANX_Request::read( 'billing_city', null ) )
				&& empty( \WC_EBANX_Request::read( $gateway_id, null )['billing_city'] ) )
			|| ( empty( \WC_EBANX_Request::read( 'billing_state', null ) )
				&& empty( \WC_EBANX_Request::read( $gateway_id, null )['billing_state'] ) )
		) {
			throw new Exception( 'INVALID-FIELDS' );
		}

		$addresses = \WC_EBANX_Request::read( 'billing_address_1', null ) ?: \WC_EBANX_Request::read( $gateway_id, null )['billing_address_1'];

		if ( ! empty( \WC_EBANX_Request::read( 'billing_address_2', null ) ) ) {
			$addresses .= ' - ' . \WC_EBANX_Request::read( 'billing_address_2', null );
		}

		$addresses     = WC_EBANX_Helper::split_street( $addresses );
		$street_number = empty( $addresses['houseNumber'] ) ? 'S/N' : trim( $addresses['houseNumber'] . ' ' . $addresses['additionToAddress'] );

		return new Address(
			[
				'address'      => $addresses['streetName'],
				'streetNumber' => $street_number,
				'city'         => \WC_EBANX_Request::read( 'billing_city', null ) ?: \WC_EBANX_Request::read( $gateway_id, null )['billing_city'],
				'country'      => Country::fromIso( $order->get_billing_country() ),
				'state'        => \WC_EBANX_Request::read( 'billing_state', null ) ?: \WC_EBANX_Request::read( $gateway_id, null )['billing_state'],
				'zipcode'      => \WC_EBANX_Request::read( 'billing_postcode', null ) ?: \WC_EBANX_Request::read( $gateway_id, null )['billing_postcode'],
			]
		);
	}

	/**
	 *
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 * @param string                  $gateway_id
	 *
	 * @return Person
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function transform_person( $order, $configs, $names, $gateway_id ) {
		$document = static::get_document( $configs, $names, $gateway_id );
		$phone_number = static::get_phone_number( $order, $configs, $gateway_id );

		return new Person(
			[
				'type'        => static::get_person_type( $configs, $names ),
				'document'    => $document,
				'email'       => $order->get_billing_email(),
				'ip'          => \WC_Geolocation::get_ip_address(),
				'name'        => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'phoneNumber' => $phone_number,
			]
		);
	}

	private static function get_phone_number( $order, $configs, $gateway_id ) {
		if ( isset( $configs->settings['checkout_custom_phone'] ) && $configs->settings['checkout_custom_phone'] === 'yes' ) {
			return \WC_EBANX_Request::read( $configs->settings['checkout_custom_phone_field'], null );
		}

		return '' !== $order->get_billing_phone() ? $order->get_billing_phone() : \WC_EBANX_Request::read( $gateway_id, null )['billing_phone'];
	}

	/**
	 *
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 * @param string                  $gateway_id
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function get_document( $configs, $names, $gateway_id ) {
		$country = trim( strtolower( WC()->customer->get_billing_country() ) );

		switch ( $country ) {
			case WC_EBANX_Constants::COUNTRY_ARGENTINA:
				return static::get_argentinian_document( $names, $gateway_id );
				break;
			case WC_EBANX_Constants::COUNTRY_BRAZIL:
				return static::get_brazilian_document( $configs, $names, $gateway_id );
				break;
			case WC_EBANX_Constants::COUNTRY_CHILE:
				return static::get_chilean_document( $names, $gateway_id );
				break;
			case WC_EBANX_Constants::COUNTRY_COLOMBIA:
				return static::get_colombian_document( $names, $gateway_id );
				break;
			case WC_EBANX_Constants::COUNTRY_PERU:
				return static::get_peruvian_document( $names, $gateway_id );
				break;
			default:
				return '';
		}
	}

	/**
	 *
	 * @param array  $names
	 * @param string $gateway_id
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function get_argentinian_document( $names, $gateway_id ) {
		$document = \WC_EBANX_Request::read( $names['ebanx_billing_argentina_document'], null );

		if ( null === $document
			&& is_array( \WC_EBANX_Request::read( $gateway_id, null ) )
			&& \WC_EBANX_Request::read( $gateway_id, null )['ebanx_billing_argentina_document'] ) {
			$document = \WC_EBANX_Request::read( $gateway_id, null )['ebanx_billing_argentina_document'];
		}

		if ( null === $document ) {
			throw new Exception( 'BP-DR-22' );
		}

		return $document;
	}

	/**
	 *
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 * @param string                  $gateway_id
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function get_brazilian_document( $configs, $names, $gateway_id ) {
		$cpf  = \WC_EBANX_Request::read( $names['ebanx_billing_brazil_document'], null ) ?: \WC_EBANX_Request::read( $gateway_id, null )['ebanx_billing_brazil_document'];
		$cnpj = \WC_EBANX_Request::read( $names['ebanx_billing_brazil_cnpj'], null ) ?: \WC_EBANX_Request::read( $gateway_id, null )['ebanx_billing_brazil_cnpj'];

		$person_type = static::get_person_type( $configs, $names );

		$has_cpf  = ! empty( $cpf );
		$has_cnpj = ! empty( $cnpj );

		if (
			( Person::TYPE_BUSINESS === $person_type
				&& ( ! $has_cnpj || ( empty( \WC_EBANX_Request::read( 'billing_company', null ) )
						&& empty( \WC_EBANX_Request::read( $gateway_id, null )['billing_company'] ) ) ) )
			|| ( Person::TYPE_PERSONAL === $person_type && ! $has_cpf )
		) {
			throw new Exception( 'INVALID-FIELDS' );
		}

		if ( Person::TYPE_BUSINESS === $person_type ) {
			return $cnpj;
		}

		return $cpf;
	}

	/**
	 *
	 * @param array  $names
	 * @param string $gateway_id
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function get_chilean_document( $names, $gateway_id ) {
		$document = \WC_EBANX_Request::read( $names['ebanx_billing_chile_document'], null )
			?: \WC_EBANX_Request::read( $gateway_id, null )['ebanx_billing_chile_document'];
		if ( null === $document ) {
			throw new Exception( 'BP-DR-22' );
		}

		return $document;
	}

	/**
	 *
	 * @param array  $names
	 * @param string $gateway_id
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function get_colombian_document( $names, $gateway_id ) {
		$document = \WC_EBANX_Request::read( $names['ebanx_billing_colombia_document'], null )
			?: \WC_EBANX_Request::read( $gateway_id, null )['ebanx_billing_colombia_document'];
		if ( null === $document ) {
			throw new Exception( 'BP-DR-22' );
		}

		return $document;
	}

	/**
	 *
	 * @param array  $names
	 * @param string $gateway_id
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function get_peruvian_document( $names, $gateway_id ) {
		$document = \WC_EBANX_Request::read( $names['ebanx_billing_peru_document'], null )
			?: \WC_EBANX_Request::read( $gateway_id, null )['ebanx_billing_peru_document'];
		if ( null === $document ) {
			throw new Exception( 'BP-DR-22' );
		}

		return $document;
	}

	/**
	 *
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param array                   $names
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	public static function get_person_type( $configs, $names ) {
		$fields_options = array();
		$person_type    = Person::TYPE_PERSONAL;

		if ( isset( $configs->settings['brazil_taxes_options'] ) && is_array( $configs->settings['brazil_taxes_options'] ) ) {
			$fields_options = $configs->settings['brazil_taxes_options'];
		}

		if ( count( $fields_options ) === 1 && 'cnpj' === $fields_options[0] ) {
			$person_type = Person::TYPE_BUSINESS;
		}

		if ( in_array( 'cpf', $fields_options ) && in_array( 'cnpj', $fields_options ) ) {
			$person_type = 'cnpj' === \WC_EBANX_Request::read( $names['ebanx_billing_brazil_person_type'], 'cpf' ) ? Person::TYPE_BUSINESS : Person::TYPE_PERSONAL;
		}

		return $person_type;
	}

	/**
	 *
	 * @param $order WC_Order $order
	 *
	 * @return array
	 */
	private static function transform_items( $order ) {
		return array_map(
			function( $product ) {
				return new Item(
					[
						'name'      => $product['name'],
						'unitPrice' => $product['line_subtotal'],
						'quantity'  => $product['qty'],
						'type'      => $product['type'],
					]
				);
			}, $order->get_items()
		);
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return Address
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function transform_address_from_post_data( $order ) {
		$order_meta_data = self::get_order_meta_data( $order );
		$order_address = [
			'postcode'  => $order_meta_data['_billing_postcode'],
			'address_1' => $order_meta_data['_billing_address_1'],
			'number'    => ! empty( $order_meta_data['_billing_number'] ) ? $order_meta_data['_billing_number'] : '',
			'address_2' => ! empty( $order_meta_data['_billing_address_2'] ) ? $order_meta_data['_billing_address_2'] : '',
			'state'     => $order_meta_data['_billing_state'],
			'city'      => ! empty( $order_meta_data['_billing_city'] ) ? $order_meta_data['_billing_city'] : '',
		];
		if (
			empty( $order_address['postcode'] )
			|| empty( $order_address['address_1'] )
			|| empty( $order_address['state'] )
		) {
			throw new Exception( 'INVALID-ADDRESS' );
		}
		$addresses = $order_address['address_1'];
		if ( ! empty( $order_address['address_2'] ) ) {
			$addresses .= ' - ' . $order_address['address_2'];
		}
		$number          = ! empty( $order_address['number'] ) ? trim( $order_address['number'] ) : 'S/N';
		$address_country = empty( $order->get_billing_country() ) ? WC_EBANX_Constants::DEFAULT_COUNTRY : $order->get_billing_country();
		return new Address(
			[
				'address'      => $addresses,
				'streetNumber' => $number,
				'city'         => $order_address['city'],
				'country'      => Country::fromIso( $address_country ),
				'state'        => $order_address['state'],
				'zipcode'      => $order_address['postcode'],
			]
		);
	}

	/**
	 *
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 *
	 * @return Payment
	 */
	public static function transform_subscription_payment( $order, $configs ) {
		return new Payment(
			[
				'amountTotal'         => $order->get_total(),
				'orderNumber'         => $order->get_id(),
				'dueDate'             => static::transform_due_date( $configs ),
				'address'             => static::transform_address_from_post_data( $order ),
				'person'              => static::transform_person_from_post_data( $order, $configs ),
				'responsible'         => static::transform_person_from_post_data( $order, $configs ),
				'instalments'         => '1',
				'items'               => static::transform_items( $order ),
				'merchantPaymentCode' => substr( $order->get_id() . '-' . md5( rand( 123123, 9999999 ) ), 0, 40 ),
				'riskProfileId'       => 'Wx' . str_replace( '.', 'x', \WC_EBANX::get_plugin_version() ),
			]
		);
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	private static function get_order_meta_data( $order ) {
		$data        = get_post_meta( $order->get_data()['id'] );
		$data_values = array();
		foreach ( $data as $key => $value ) {
			$data_values[ $key ] = reset( $value );
		}
		return $data_values;
	}
	/**
	 *
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param string                  $user_cc_token
	 *
	 * @return Payment
	 */
	public static function transform_card_subscription_payment( $order, $configs, $user_cc_token ) {
		$payment = self::transform_subscription_payment( $order, $configs );
		$payment->card = new Card(
			[
				'token' => $user_cc_token,
			]
		);
		$payment->manualReview = 'yes' === $configs->settings['manual_review_enabled']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		return $payment;
	}


	/**
	 *
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 *
	 * @return string
	 */
	private static function get_person_type_from_order( $order, $configs ) {
		$fields_options = array();
		if ( isset( $configs->settings['brazil_taxes_options'] ) && is_array( $configs->settings['brazil_taxes_options'] ) ) {
			$fields_options = $configs->settings['brazil_taxes_options'];
		}
		if ( count( $fields_options ) === 1 && 'cnpj' === $fields_options[0] ) {
			return Person::TYPE_BUSINESS;
		}
		$brazil_person_type = get_post_meta( $order->get_id(), '_billing_persontype', true );
		if ( empty( $brazil_person_type ) ) {
			return Person::TYPE_PERSONAL;
		}
		if ( 'cpf' === $brazil_person_type || 1 == $brazil_person_type || 'pessoa física' === strtolower( $brazil_person_type ) ) {
			return Person::TYPE_PERSONAL;
		}
		return Person::TYPE_BUSINESS;
	}

	private static function get_brazilian_document_by_user_id( $user_id ) {
		$cpf  = get_user_meta( $user_id, '_ebanx_billing_brazil_document', true );
		$cnpj = get_user_meta( $user_id, '_ebanx_billing_brazil_cnpj', true );

		if (! empty( $cpf )) {
			return $cpf;
		}

		if (! empty( $cnpj )) {
			return $cnpj;
		}

		throw new Exception( 'WC_EBANX_Payment_Adapter: BRAZILIAN DOCUMENT NOT FOUND' );
	}

	/**
	 *
	 * @param WC_Order $order
	 * @param string   $person_type
	 *
	 * @return string
	 * @throws Exception Throws parameter missing exception.
	 */
	private static function get_document_from_order( $order ) {
		$country = trim( $order->get_billing_country() );
		$user_id = get_post_meta( $order->get_id(), '_customer_user', true );

		switch ( $country ) {
			case 'AR':
				return get_user_meta( $user_id, '_ebanx_billing_argentina_document', true );
				break;
			case 'BR':
				return self::get_brazilian_document_by_user_id( $user_id );
				break;
			case 'CL':
				return get_user_meta( $user_id, '_ebanx_billing_chile_document', true );
				break;
			case 'CO':
				return get_user_meta( $user_id, '_ebanx_billing_colombia_document', true );
				break;
			case 'PE':
				return get_user_meta( $user_id, '_ebanx_billing_peru_document', true );
				break;
			default:
				throw new Exception( 'WC_EBANX_Payment_Adapter: INVALID-DOCUMENT' );
		}
	}

	/**
	 *
	 * @param WC_Order                $order
	 * @param WC_EBANX_Global_Gateway $configs
	 *
	 * @return Person
	 */
	private static function transform_person_from_post_data( $order, $configs ) {
		$person_type = self::get_person_type_from_order( $order, $configs );
		$document    = self::get_document_from_order( $order, $person_type );
		return new Person(
			[
				'type'        => $person_type,
				'document'    => $document,
				'email'       => $order->get_billing_email(),
				'ip'          => \WC_Geolocation::get_ip_address(),
				'name'        => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'phoneNumber' => $order->get_billing_phone(),
			]
		);
	}
}
