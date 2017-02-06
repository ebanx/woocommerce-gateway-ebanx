<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_EBANX_Gateway_Utils class.
 *
 */
abstract class WC_EBANX_Gateway_Utils
{
    /**
     * Countries that EBANX process
     */
    const COUNTRY_PERU     = 'pe';
    const COUNTRY_CHILE    = 'cl';
    const COUNTRY_BRAZIL   = 'br';
    const COUNTRY_MEXICO   = 'mx';
    const COUNTRY_COLOMBIA = 'co';

    /**
     * Currencies that EBANX processes
     */
    const CURRENCY_CODE_BRL = 'BRL';
    const CURRENCY_CODE_USD = 'USD';
    const CURRENCY_CODE_EUR = 'EUR';
    const CURRENCY_CODE_PEN = 'PEN';
    const CURRENCY_CODE_MXN = 'MXN';
    const CURRENCY_CODE_COP = 'COP';
    const CURRENCY_CODE_CLP = 'CLP';

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
      'safetypay-cash' => 'America/Lima'
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

    /**
     * The Brazil taxes available options that EBANX process
     * 
     * @var array
     */
    public static $BRAZIL_TAXES_ALLOWED = array('cpf', 'cnpj');

    /**
     * Types allowed by SafetyPay
     *
     * @var array
     */
    public static $TYPES_SAFETYPAY_ALLOWED = array(
        'cash', 'online',
    );

    /**
     * This function splits an address line like for example "Pallaswiesenstr. 45 App 231" into its individual parts.
     * Supported parts are additionToAddress1, streetName, houseNumber and additionToAddress2. AdditionToAddress1
     * and additionToAddress2 contain additional information that is given at the start and the end of the string, respectively.
     * Unit tests for testing the regular expression that this function uses exist over at https://regex101.com/r/vO5fY7/1.
     * More information on this functionality can be found at http://blog.viison.com/post/115849166487/shopware-5-from-a-technical-point-of-view#address-splitting.
     *
     * @param string $address
     * @return array
     */
    public static function split_street($address)
    {
        $regex = '/\A\s*
           (?: #########################################################################
               # Option A: [<Addition to address 1>] <House number> <Street name>      #
               # [<Addition to address 2>]                                             #
               #########################################################################
               (?:(?P<A_Addition_to_address_1>.*?),\s*)? # Addition to address 1
           (?:No\.\s*)?
               (?P<A_House_number>\pN+[a-zA-Z]?(?:\s*[-\/\pP]\s*\pN+[a-zA-Z]?)*) # House number
           \s*,?\s*
               (?P<A_Street_name>(?:[a-zA-Z]\s*|\pN\pL{2,}\s\pL)\S[^,#]*?(?<!\s)) # Street name
           \s*(?:(?:[,\/]|(?=\#))\s*(?!\s*No\.)
               (?P<A_Addition_to_address_2>(?!\s).*?))? # Addition to address 2
           |   #########################################################################
               # Option B: [<Addition to address 1>] <Street name> <House number>      #
               # [<Addition to address 2>]                                             #
               #########################################################################
               (?:(?P<B_Addition_to_address_1>.*?),\s*(?=.*[,\/]))? # Addition to address 1
               (?!\s*No\.)(?P<B_Street_name>[^0-9# ]\s*\S(?:[^,#](?!\b\pN+\s))*?(?<!\s)) # Street name
           \s*[\/,]?\s*(?:\sNo[.:])?\s*
               (?P<B_House_number>\pN+\s*-?[a-zA-Z]?(?:\s*[-\/\pP]?\s*\pN+(?:\s*[\-a-zA-Z])?)*|
               [IVXLCDM]+(?!.*\b\pN+\b))(?<!\s) # House number
           \s*(?:(?:[,\/]|(?=\#)|\s)\s*(?!\s*No\.)\s*
               (?P<B_Addition_to_address_2>(?!\s).*?))? # Addition to address 2
           )
           \s*\Z/xu';

        $result = preg_match($regex, $address, $matches);

        if ($result === 0) {
            throw new \InvalidArgumentException(sprintf('Address \'%s\' could not be splitted into street name and house number', $address));
        } elseif ($result === false) {
            throw new \RuntimeException(sprintf('Error occurred while trying to split address \'%s\'', $address));
        }

        if (!empty($matches['A_Street_name'])) {
            return array(
                'additionToAddress1' => $matches['A_Addition_to_address_1'],
                'streetName' => $matches['A_Street_name'],
                'houseNumber' => $matches['A_House_number'],
                'additionToAddress2' => (isset($matches['A_Addition_to_address_2'])) ? $matches['A_Addition_to_address_2'] : ''
            );
        } else {
            return array(
                'additionToAddress1' => $matches['B_Addition_to_address_1'],
                'streetName' => $matches['B_Street_name'],
                'houseNumber' => $matches['B_House_number'],
                'additionToAddress2' => isset($matches['B_Addition_to_address_2']) ? $matches['B_Addition_to_address_2'] : ''
            );
        }
    }
}
