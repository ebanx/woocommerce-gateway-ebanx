<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Ebanx_Gateway_Utils class.
 *
 */
abstract class WC_Ebanx_Gateway_Utils {
    const COUNTRY_BRAZIL = 'br';
    const COUNTRY_MEXICO = 'mx';
    const COUNTRY_CHILE  = 'cl';

    const CURRENCY_CODE_BRL = 'BRL';
    const CURRENCY_CODE_USD = 'USD';
    const CURRENCY_CODE_EUR = 'EUR';
    const CURRENCY_CODE_PEN = 'PEN';
    const CURRENCY_CODE_MXN = 'MXN';
    const CURRENCY_CODE_COP = 'COP';
    const CURRENCY_CODE_CLP = 'CLP';

    static $BANKS_TEF_ALLOWED = array(
      self::COUNTRY_BRAZIL => array('bancodobrasil', 'itau', 'bradesco', 'banrisul')
    );
}
