<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * WC_EBANX_Constants class.
 *
 */
abstract class WC_EBANX_Constants
{
	/**
	 * Countries that EBANX process
	 */
	const COUNTRY_PERU     = 'pe';
	const COUNTRY_CHILE    = 'cl';
	const COUNTRY_BRAZIL   = 'br';
	const COUNTRY_MEXICO   = 'mx';
	const COUNTRY_COLOMBIA = 'co';

	const COUNTRY_BRAZIL_NAME = 'Brazil';
	const COUNTRY_CHILE_NAME = 'Chile';
	const COUNTRY_COLOMBIA_NAME = 'Colombia';
	const COUNTRY_PERU_NAME = 'Peru';
	const COUNTRY_MEXICO_NAME = 'Mexico';

	/**
	 * Currencies that EBANX processes
	 */
	const CURRENCY_CODE_BRL = 'BRL'; // Brazil
	const CURRENCY_CODE_USD = 'USD'; // USA
	const CURRENCY_CODE_EUR = 'EUR'; // Euro
	const CURRENCY_CODE_PEN = 'PEN'; // Peru
	const CURRENCY_CODE_MXN = 'MXN'; // Mexico
	const CURRENCY_CODE_COP = 'COP'; // Colombia
	const CURRENCY_CODE_CLP = 'CLP'; // Chile
	public static $CURRENCIES_CODES_ALLOWED = array(
		self::CURRENCY_CODE_BRL,
		self::CURRENCY_CODE_USD,
		self::CURRENCY_CODE_EUR,
		self::CURRENCY_CODE_PEN,
		self::CURRENCY_CODE_MXN,
		self::CURRENCY_CODE_COP,
		self::CURRENCY_CODE_CLP
	);

	/**
	 *  Local currencies that EBANX processes
	 */
	public static $LOCAL_CURRENCIES = array(
		self::CURRENCY_CODE_BRL,
		self::CURRENCY_CODE_CLP,
		self::CURRENCY_CODE_COP,
		self::CURRENCY_CODE_MXN
	);

	/**
	 * Minimal instalment value for acquirers to approve based on currency
	 */
	const ACQUIRER_MIN_INSTALMENT_VALUE_MXN = 100;
	const ACQUIRER_MIN_INSTALMENT_VALUE_BRL = 20;

	/**
	 * Max supported credit-card instalments
	 */
	const MAX_INSTALMENTS = 12;

	/**
	 * The list of all countries that EBANX processes
	 *
	 * @var array
	 */
	public static $ALL_COUNTRIES = array(
		self::COUNTRY_BRAZIL,
		self::COUNTRY_COLOMBIA,
		self::COUNTRY_MEXICO,
		self::COUNTRY_PERU,
		self::COUNTRY_CHILE,
	);

	/**
	 * The countries that credit cards are processed by EBANX
	 *
	 * @var array
	 */
	public static $CREDIT_CARD_COUNTRIES = array(
		self::COUNTRY_BRAZIL => self::COUNTRY_BRAZIL,
		self::COUNTRY_MEXICO => self::COUNTRY_MEXICO,
	);

	/**
	 * The timezones of the countries processed by EBANX
	 *
	 * @var array
	 */
	public static $CASH_PAYMENTS_TIMEZONES = array(
	  'boleto' => 'America/Sao_Paulo',
	  'oxxo' => 'America/Mexico_City',
	  'pagoefectivo' => 'America/Lima',
	  'sencillito' => 'America/Santiago',
	  'safetypay-cash' => 'America/Lima',
	  'baloto' => 'America/Bogota',
	);

	/**
	 * The banks that EBANX process in Brazil
	 *
	 * @var array
	 */
	public static $BANKS_TEF_ALLOWED = array(
		self::COUNTRY_BRAZIL => array('bancodobrasil', 'itau', 'bradesco', 'banrisul'),
	);

	/**
	 * The banks that EBANX process in Colombia
	 *
	 * @var array
	 */
	public static $BANKS_EFT_ALLOWED = array(
		self::COUNTRY_COLOMBIA => array(
			'banco_agrario' => 'Banco Agrario',
			'banco_av_villas' => 'Banco AV Villas',
			'banco_bbva_colombia_s.a.' => 'Banco BBVA Colombia',
			'banco_caja_social' => 'Banco Caja Social',
			'banco_colpatria' => 'Banco Colpatria',
			'banco_cooperativo_coopcentral' => 'Banco Cooperativo Coopcentral',
			'banco_corpbanca_s.a' => 'Banco CorpBanca Colombia',
			'banco_davivienda' => 'Banco Davivienda',
			'banco_de_bogota' => 'Banco de Bogotá',
			'banco_de_occidente' => 'Banco de Occidente',
			'banco_falabella_' => 'Banco Falabella',
			'banco_gnb_sudameris' => 'Banco GNB Sudameris',
			'banco_pichincha_s.a.' => 'Banco Pichincha',
			'banco_popular' => 'Banco Popular',
			'banco_procredit' => 'Banco ProCredit',
			'bancolombia' => 'Bancolombia',
			'bancoomeva_s.a.' => 'Bancoomeva',
			'citibank_' => 'Citibank',
			'helm_bank_s.a.' => 'Helm Bank',
		)
	);

	public static $GATEWAY_TO_PAYMENT_TYPE_CODE = array(
		'ebanx-banking-ticket' => '_boleto',
		'ebanx-credit-card-br' => '_creditcard',
		'ebanx-credit-card-mx' => '_creditcard',
		'ebanx-debit-card' => '_debitcard',
		'ebanx-oxxo' => '_oxxo',
		'ebanx-sencillito' => '_sencillito',
		'ebanx-servipag' => 'servipag',
		'ebanx-tef' => '_tef',
		'ebanx-pagoefectivo' => '_pagoefectivo',
		'ebanx-safetypay' => '_safetypay',
		'ebanx-eft' => 'eft',
		'ebanx-baloto' => '_baloto'
		//'ebanx-account' => '_ebanxaccount'
	);

	/**
	 * The Brazil taxes available options that EBANX process
	 *
	 * @var array
	 */
	public static $BRAZIL_TAXES_ALLOWED = array('cpf', 'cnpj');

	/**
	 * The gateways that plugin uses as identification
	 *
	 * @var array
	 */
	public static $EBANX_GATEWAYS_BY_COUNTRY = array(
		self::COUNTRY_BRAZIL => array(
			'ebanx-banking-ticket',
			'ebanx-credit-card-br',
			'ebanx-tef',
			'ebanx-account'
		),
		self::COUNTRY_CHILE => array(
			'ebanx-sencillito',
			'ebanx-servipag',
		),
		self::COUNTRY_COLOMBIA => array(
			'ebanx-baloto',
			'ebanx-eft',
		),
		self::COUNTRY_PERU => array(
			'ebanx-pagoefectivo',
			'ebanx-safetypay',
		),
		self::COUNTRY_MEXICO => array(
			'ebanx-credit-card-mx',
			'ebanx-debit-card',
			'ebanx-oxxo'
		)
	);

	/**
	 * Types allowed by SafetyPay
	 *
	 * @var array
	 */
	public static $TYPES_SAFETYPAY_ALLOWED = array(
		'cash', 'online',
	);

	/**
	 * Flatten an array
	 *
	 * @param  array  $array The array to flatten
	 * @return array        The new array flatted
	 */
	public static function flatten(array $array) {
		$return = array();
		array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });

		return $return;
	}

	/**
	 * Splits address in street name, house number and addition
	 *
	 * @param  string $address Address to be split
	 * @return array
	 */
	public static function split_street($address) {
		$result = preg_match('/^([^,\-\/\#0-9]*)\s*[,\-\/\#]?\s*([0-9]+)\s*[,\-\/]?\s*([^,\-\/]*)(\s*[,\-\/]?\s*)([^,\-\/]*)$/', $address, $matches);

		if ($result === false) {
			throw new \RuntimeException(sprintf('Problems trying to parse address: \'%s\'', $address));
		}

		if ($result === 0) {
			return array(
				'streetName' => $address,
				'houseNumber' => '',
				'additionToAddress' => ''
			);
		}

		$street_name = $matches[1];
		$house_number = $matches[2];
		$addition_to_address = $matches[3] . $matches[4] . $matches[5];

		if (empty($street_name)) {
			$street_name = $matches[3];
			$addition_to_address = $matches[5];
		}

		return array(
			'streetName' => $street_name,
			'houseNumber' => $house_number,
			'additionToAddress' => $addition_to_address
		);
	}
}
