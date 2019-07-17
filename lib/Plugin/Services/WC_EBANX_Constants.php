<?php

namespace EBANX\Plugin\Services;

use Ebanx\Benjamin\Models\Country;

abstract class WC_EBANX_Constants {

	const COUNTRY_PERU      = 'pe';
	const COUNTRY_CHILE     = 'cl';
	const COUNTRY_BRAZIL    = 'br';
	const COUNTRY_MEXICO    = 'mx';
	const COUNTRY_COLOMBIA  = 'co';
	const COUNTRY_ARGENTINA = 'ar';
	const COUNTRY_ECUADOR   = 'ec';
	const COUNTRY_BOLIVIA   = 'bo';

	const SETTINGS_URL = 'admin.php?page=wc-settings&tab=checkout&section=ebanx-global';

	const CURRENCY_CODE_BRL = 'BRL'; // Brazil.
	const CURRENCY_CODE_USD = 'USD'; // USA & ECUADOR.
	const CURRENCY_CODE_EUR = 'EUR'; // European Union.
	const CURRENCY_CODE_PEN = 'PEN'; // Peru.
	const CURRENCY_CODE_MXN = 'MXN'; // Mexico.
	const CURRENCY_CODE_COP = 'COP'; // Colombia.
	const CURRENCY_CODE_CLP = 'CLP'; // Chile.
	const CURRENCY_CODE_ARS = 'ARS'; // Argentina.
	const CURRENCY_CODE_BOB = 'BOB'; // Bolivia.

	const COUNTRY_NAME_FROM_ABBREVIATION = [
		self::COUNTRY_BRAZIL    => Country::BRAZIL,
		self::COUNTRY_MEXICO    => Country::MEXICO,
		self::COUNTRY_COLOMBIA  => Country::COLOMBIA,
		self::COUNTRY_ARGENTINA => Country::ARGENTINA,
		self::COUNTRY_BOLIVIA   => Country::BOLIVIA,
	];

	public static $allowed_currency_codes = [
		self::CURRENCY_CODE_BRL,
		self::CURRENCY_CODE_USD,
		self::CURRENCY_CODE_EUR,
		self::CURRENCY_CODE_PEN,
		self::CURRENCY_CODE_MXN,
		self::CURRENCY_CODE_COP,
		self::CURRENCY_CODE_CLP,
		self::CURRENCY_CODE_ARS,
		self::CURRENCY_CODE_BOB,
	];

	public static $local_currencies = [
		self::COUNTRY_BRAZIL    => self::CURRENCY_CODE_BRL,
		self::COUNTRY_CHILE     => self::CURRENCY_CODE_CLP,
		self::COUNTRY_COLOMBIA  => self::CURRENCY_CODE_COP,
		self::COUNTRY_MEXICO    => self::CURRENCY_CODE_MXN,
		self::COUNTRY_PERU      => self::CURRENCY_CODE_PEN,
		self::COUNTRY_ARGENTINA => self::CURRENCY_CODE_ARS,
		self::COUNTRY_BOLIVIA   => self::CURRENCY_CODE_BOB,
	];

	const ACQUIRER_MIN_INSTALMENT_VALUE_MXN = 100;
	const ACQUIRER_MIN_INSTALMENT_VALUE_BRL = 5;
	const ACQUIRER_MIN_INSTALMENT_VALUE_COP = 0;
	const ACQUIRER_MIN_INSTALMENT_VALUE_ARS = 0;

	public static $max_instalments = [
		self::COUNTRY_BRAZIL    => 12,
		self::COUNTRY_MEXICO    => 12,
		self::COUNTRY_COLOMBIA  => 36,
		self::COUNTRY_ARGENTINA => 12,
	];

	const BRAZIL_TAX = 0.0038;

	public static $all_countries = [
		self::COUNTRY_BRAZIL,
		self::COUNTRY_COLOMBIA,
		self::COUNTRY_MEXICO,
		self::COUNTRY_PERU,
		self::COUNTRY_CHILE,
		self::COUNTRY_ARGENTINA,
		self::COUNTRY_BOLIVIA,
	];

	public static $credit_card_countries = [
		self::COUNTRY_ARGENTINA => self::COUNTRY_ARGENTINA,
		self::COUNTRY_BRAZIL    => self::COUNTRY_BRAZIL,
		self::COUNTRY_COLOMBIA  => self::COUNTRY_COLOMBIA,
		self::COUNTRY_MEXICO    => self::COUNTRY_MEXICO,
	];

	public static $credit_card_currencies = [
		self::CURRENCY_CODE_BRL,
		self::CURRENCY_CODE_MXN,
		self::CURRENCY_CODE_USD,
		self::CURRENCY_CODE_COP,
		self::CURRENCY_CODE_EUR,
		self::CURRENCY_CODE_ARS,
	];

	public static $cash_payment_gateways_code = [
		'ebanx-banking-ticket',
		'ebanx-oxxo',
		'ebanx-spei',
		'ebanx-pagoefectivo',
		'ebanx-sencillito',
		'ebanx-safetypay-cash',
		'ebanx-baloto',
		'ebanx-efectivo',
		'ebanx-banktransfer',
		'ebanx-pagosnet',
	];

	public static $banks_tef_allowed = [
		self::COUNTRY_BRAZIL => ['bancodobrasil', 'itau', 'bradesco', 'banrisul'],
	];

	public static $banks_eft_allowed = [
		self::COUNTRY_COLOMBIA => [
			'banco_agrario'                 => 'Banco Agrario',
			'banco_av_villas'               => 'Banco AV Villas',
			'banco_bbva_colombia_s.a.'      => 'Banco BBVA Colombia',
			'banco_caja_social'             => 'Banco Caja Social',
			'banco_colpatria'               => 'Banco Colpatria',
			'banco_cooperativo_coopcentral' => 'Banco Cooperativo Coopcentral',
			'banco_corpbanca_s.a'           => 'Banco CorpBanca Colombia',
			'banco_davivienda'              => 'Banco Davivienda',
			'banco_de_bogota'               => 'Banco de Bogotá',
			'banco_de_occidente'            => 'Banco de Occidente',
			'banco_falabella_'              => 'Banco Falabella',
			'banco_gnb_sudameris'           => 'Banco GNB Sudameris',
			'banco_pichincha_s.a.'          => 'Banco Pichincha',
			'banco_popular'                 => 'Banco Popular',
			'banco_procredit'               => 'Banco ProCredit',
			'bancolombia'                   => 'Bancolombia',
			'bancoomeva_s.a.'               => 'Bancoomeva',
			'citibank_'                     => 'Citibank',
			'helm_bank_s.a.'                => 'Helm Bank',
		],
	];

	public static $vouchers_efectivo_allowed = [
		'rapipago',
		'pagofacil',
		'cupon',
	];

	public static $gateway_to_payment_type_code = [
		'ebanx-bank_transfer'  => '_bank_transfer',
		'ebanx-banking-ticket' => '_boleto',
		'ebanx-credit-card-br' => '_creditcard',
		'ebanx-credit-card-mx' => '_creditcard',
		'ebanx-debit-card'     => 'debitcard',
		'ebanx-oxxo'           => '_oxxo',
		'ebanx-sencillito'     => '_sencillito',
		'ebanx-servipag'       => 'servipag',
		'ebanx-tef'            => '_tef',
		'ebanx-pagoefectivo'   => '_pagoefectivo',
		'ebanx-safetypay'      => '_safetypay',
		'ebanx-eft'            => 'eft',
		'ebanx-baloto'         => '_baloto',
		'ebanx-pagosnet'       => 'pagosnet',
	];

	public static $brazil_taxes_allowed = ['cpf', 'cnpj'];

	public static $ebanx_gateways_by_country = [
		self::COUNTRY_BRAZIL    => [
			'ebanx-banking-ticket',
			'ebanx-credit-card-br',
			'ebanx-tef',
			'ebanx-banktransfer',
		],
		self::COUNTRY_CHILE     => [
			'ebanx-webpay',
			'ebanx-multicaja',
			'ebanx-sencillito',
			'ebanx-servipag',
		],
		self::COUNTRY_COLOMBIA  => [
			'ebanx-credit-card-co',
			'ebanx-baloto',
			'ebanx-eft',
		],
		self::COUNTRY_PERU      => [
			'ebanx-pagoefectivo',
			'ebanx-safetypay',
		],
		self::COUNTRY_MEXICO    => [
			'ebanx-credit-card-mx',
			'ebanx-debit-card',
			'ebanx-oxxo',
			'ebanx-spei',
		],
		self::COUNTRY_ARGENTINA => [
			'ebanx-efectivo',
		],
		self::COUNTRY_BOLIVIA   => [
			'ebanx-pagosnet',
		]
	];

	public static $safetypay_allowed_types = [
		'cash',
		'online',
	];
}
