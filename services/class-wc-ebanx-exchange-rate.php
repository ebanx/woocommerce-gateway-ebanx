<?php
require_once WC_EBANX_DIR . 'woocommerce-gateway-ebanx.php';

/**
 * Class WC_EBANX_Exchange_Rate Handles exchange rates
 */
class WC_EBANX_Exchange_Rate {

	/**
	 *
	 * @var WC_EBANX_Global_Gateway
	 */
	private $configs;

	/**
	 * WC_EBANX_Exchange_Rate constructor.
	 */
	public function __construct() {
		$this->configs = new WC_EBANX_Global_Gateway();
	}

	/**
	 * Create the converter amount on checkout page
	 *
	 * @param string  $currency Possible currencies: BRL, USD, EUR, PEN, CLP, COP, MXN.
	 * @param boolean $template
	 * @param boolean $country
	 * @param boolean $instalments
	 *
	 * @return string|null
	 * @throws Exception Throws missing parameter exception.
	 */
	public function checkout_rate_conversion( $currency, $template = true, $country = null, $instalments = null ) {
		if ( ! in_array( $this->configs->merchant_currency, WC_EBANX_Constants::$allowed_currency_codes )
			 || 'yes' !== $this->configs->get_setting_or_default( 'show_local_amount', 'yes' ) ) {
			return null;
		}

		$amount = WC()->cart->total;

		try {
			$amount = apply_filters( 'ebanx_get_custom_total_amount', $amount, $instalments );
		} catch ( Exception $e ) {
			WC_EBANX::log( $e->getMessage() );
		}

		$order_id = null;

		if ( ! empty( get_query_var( 'order-pay' ) ) ) {
			$order_id = get_query_var( 'order-pay' );
		} elseif ( WC_EBANX_Request::has( 'order_id' ) && ! empty( WC_EBANX_Request::read( 'order_id', null ) ) ) {
			$order_id = WC_EBANX_Request::read( 'order_id', null );
		}

		if ( ! is_null( $order_id ) ) {
			$order = new WC_Order( $order_id );

			$amount = $order->get_total();
		}

		if ( null === $country ) {
			$country = trim( strtolower( WC()->customer->get_country() ) );
		}

		$rate = 1;
		if ( in_array( $this->configs->merchant_currency, [ WC_EBANX_Constants::CURRENCY_CODE_USD, WC_EBANX_Constants::CURRENCY_CODE_EUR ] ) ) {
			$rate = round( floatval( $this->get_local_currency_rate_for_site( $currency ) ), 2 );

			if ( WC()->cart->prices_include_tax ) {
				$amount += WC()->cart->tax_total;
			}
		}

		$amount *= $rate;

		if ( 'yes' === $this->configs->get_setting_or_default( 'interest_rates_enabled', 'no' ) && null !== $instalments ) {
			$interest_rate = floatval( $this->configs->settings[ 'interest_rates_' . sprintf( '%02d', $instalments ) ] );

			$amount += ( $amount * $interest_rate / 100 );
		}

		if ( WC_EBANX_Constants::COUNTRY_BRAZIL === $country && 'yes' === $this->configs->get_setting_or_default( 'add_iof_to_local_amount_enabled', 'yes' ) ) {
			$amount += ( $amount * WC_EBANX_Constants::BRAZIL_TAX );
		}

		if ( null !== $instalments ) {
			$instalment_price = $amount / $instalments;
			$instalment_price = round( floatval( $instalment_price ), 2 );
			$amount           = $instalment_price * $instalments;
		}

		$message               = WC_EBANX_Constants::get_checkout_message( $amount, $currency, $country, $this->configs );
		$exchange_rate_message = $this->get_exchange_rate_message( $rate, $currency, $country );

		if ( $template ) {
			wc_get_template(
				'checkout-conversion-rate.php',
				[
					'message'               => $message,
					'exchange_rate_message' => $exchange_rate_message,
				],
				'woocommerce/ebanx/',
				WC_EBANX::get_templates_path()
			);
		}

		return $message;
	}

	/**
	 *
	 * @param double $rate
	 * @param string $currency
	 * @param string $country
	 *
	 * @return string
	 */
	public function get_exchange_rate_message( $rate, $currency, $country ) {
		if ( $this->configs->get_setting_or_default( 'show_exchange_rate', 'no' ) === 'no' ) {
			return '';
		}

		if ( 1 === $rate ) {
			return '';
		}

		$price    = wc_price( $rate, array( 'currency' => $currency ) );
		$language = WC_EBANX_Constants::get_language_by_country( $country );
		$texts    = array(
			'pt-br' => 'Taxa de câmbio: ',
			'es'    => 'Tipo de cambio: ',
		);

		$message = $texts[ $language ];
		$message .= '<strong class="ebanx-exchange-rate">' . $price . '</strong>';

		return $message;
	}

	/**
	 * Queries for a currency exchange rate against USD
	 *
	 * @param string $local_currency_code
	 * @return double
	 */
	public function get_currency_rate( $local_currency_code ) {
		$cache_key = 'EBANX_exchange_' . $local_currency_code;

		$cache_time = date( 'YmdH' ) . floor( date( 'i' ) / 5 );

		$cached = get_option( $cache_key );
		if ( false !== $cached ) {
			list( $rate, $time ) = explode( '|', $cached );
			if ( $time === $cache_time ) {
				return $rate;
			}
		}
		$ebanx = ( new WC_EBANX_Api( $this->configs ) )->ebanx();

		$rate = $ebanx->exchange()->siteToLocal( $local_currency_code );
		update_option( $cache_key, $rate . '|' . $cache_time );
		return $rate;
	}

	/**
	 * Queries for a currency exchange rate against site currency
	 *
	 * @param  string $local_currency_code
	 * @return double
	 */
	public function get_local_currency_rate_for_site( $local_currency_code ) {
		if ( strtoupper( $local_currency_code ) === $this->configs->merchant_currency ) {
			return 1;
		}

		$usd_to_site_rate     = 1;
		$converted_currencies = [
			WC_EBANX_Constants::CURRENCY_CODE_USD,
			WC_EBANX_Constants::CURRENCY_CODE_EUR,
		];

		if ( ! in_array( $this->configs->merchant_currency, $converted_currencies ) ) {
			$usd_to_site_rate = $this->get_currency_rate( $this->configs->merchant_currency );
		}

		return $this->get_currency_rate( $local_currency_code ) / $usd_to_site_rate;
	}
}
