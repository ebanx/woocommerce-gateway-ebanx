<?php

namespace EBANX\Plugin\Services;

use Ebanx\Benjamin\Models\Configs\Config;
use Ebanx\Benjamin\Models\Configs\CreditCardConfig;
use WC_EBANX;
use WC_EBANX_Global_Gateway;

/**
 * Class WC_EBANX_Api
 */
class WC_EBANX_Api {
	/**
	 *
	 * @var \Ebanx\Benjamin\Facade
	 */
	protected $ebanx;

	/**
	 *
	 * @var WC_EBANX_Global_Gateway
	 */
	protected $configs;

	/**
	 *
	 * @var string
	 */
	protected $currency;

	/**
	 * EBANX_Api constructor.
	 *
	 * @param WC_EBANX_Global_Gateway $configs
	 * @param string                  $currency
	 */
	public function __construct( WC_EBANX_Global_Gateway $configs, $currency = null ) {
		$this->configs = $configs;
		$this->currency = is_null( $currency ) ? strtoupper( get_woocommerce_currency() ) : $currency;
		$this->ebanx   = EBANX( $this->get_config( $currency ), $this->get_credit_card_config( 'br' ) );
		$this->ebanx->setSource( 'WooCommerce', WC_EBANX::get_plugin_version() );
	}

	/**
	 *
	 * @param string $currency
	 *
	 * @return Config
	 */
	private function get_config( $currency ) {
		return new Config(
			array(
				'integrationKey'        => $this->configs->settings['live_private_key'] ?? '',
				'sandboxIntegrationKey' => $this->configs->settings['sandbox_private_key'] ?? '',
				'isSandbox'             => ! isset( $this->configs->settings['sandbox_mode_enabled'] ) || 'yes' === $this->configs->settings['sandbox_mode_enabled'],
				'baseCurrency'          => $this->currency,
				'notificationUrl'       => esc_url( home_url( '/' ) ),
				'redirectUrl'           => esc_url( home_url( '/' ) ),
			)
		);
	}

	/**
	 *
	 * @param string $country_abbr
	 *
	 * @return CreditCardConfig
	 */
	private function get_credit_card_config( $country_abbr ) {
		$currency_code = strtolower( get_woocommerce_currency() );

		$credit_card_config = new CreditCardConfig(
			array(
				'maxInstalments'      => $this->configs->settings[ "{$country_abbr}_credit_card_instalments" ] ?? 1,
				'minInstalmentAmount' => $this->configs->settings["{$country_abbr}_min_instalment_value_$currency_code"] ?? 1,
			)
		);

		for ( $i = 1; $i <= $this->configs->settings[ "{$country_abbr}_credit_card_instalments" ]; $i++ ) {
			$key = "{$country_abbr}_interest_rates_" . sprintf( '%02d', $i );
			$interest = 0;

			if (isset($this->configs->settings[ $key ])) {
				$interest = $this->configs->settings[ $key ];
			}

			$credit_card_config->addInterest( $i, floatval( $interest ) );
		}

		return $credit_card_config;
	}

	/**
	 *
	 * @return \Ebanx\Benjamin\Facade
	 */
	public function ebanx() {
		return $this->ebanx;
	}
}
