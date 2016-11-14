<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_Ebanx_Gateway_Utils class.
 *
 */
abstract class WC_Ebanx_Gateway_Utils
{
    const COUNTRY_PERU     = 'pe';
    const COUNTRY_CHILE    = 'cl';
    const COUNTRY_BRAZIL   = 'br';
    const COUNTRY_MEXICO   = 'mx';
    const COUNTRY_COLOMBIA = 'co';

    const CURRENCY_CODE_BRL = 'BRL';
    const CURRENCY_CODE_USD = 'USD';
    const CURRENCY_CODE_EUR = 'EUR';
    const CURRENCY_CODE_PEN = 'PEN';
    const CURRENCY_CODE_MXN = 'MXN';
    const CURRENCY_CODE_COP = 'COP';
    const CURRENCY_CODE_CLP = 'CLP';

    public static $BANKS_TEF_ALLOWED = array(
        self::COUNTRY_BRAZIL => array('bancodobrasil', 'itau', 'bradesco', 'banrisul'),
    );

    public static $BANKS_EFT_ALLOWED = array(
        self::COUNTRY_COLOMBIA => array(
            'banco_agrario',
            'banco_av_villas',
            'banco_bbva_colombia_s.a.',
            'banco_caja_social',
            'banco_colpatria',
            'banco_cooperativo_coopcentral',
            'banco_corpbanca_s.a',
            'banco_davivienda',
            'banco_de_bogota',
            'banco_de_occidente',
            'banco_falabella_',
            'banco_gnb_sudameris',
            'banco_pichincha_s.a.',
            'banco_popular',
            'banco_procredit',
            'bancolombia',
            'bancoomeva_s.a.',
            'citibank_',
            'helm_bank_s.a.',
        ),
    );

    public static $TYPES_SAFETYPAY_ALLOWED = array(
        'cash', 'online',
    );

    public static function isTef($paymentTypeCode)
    {
        return in_array(strtolower($paymentTypeCode), call_user_func_array('array_merge', self::$BANKS_TEF_ALLOWED));
    }
}
