<?php
require_once WC_EBANX_VENDOR_DIR . '/autoload.php';
require_once WC_EBANX_DIR . 'woocommerce-gateway-ebanx.php';

use Ebanx\Benjamin\Models\Configs\Config;
use Ebanx\Benjamin\Models\Configs\CreditCardConfig;

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
	 * EBANX_Api constructor.
	 *
	 * @param WC_EBANX_Global_Gateway $configs
	 */
	public function __construct( WC_EBANX_Global_Gateway $configs ) {
		$this->configs = $configs;
		$this->ebanx   = EBANX( $this->get_config() );
	}

	/**
	 *
	 * @return Config
	 */
	private function get_config() {
		return new Config(
			array(
				'integrationKey'        => $this->configs->settings['live_private_key'],
				'sandboxIntegrationKey' => $this->configs->settings['sandbox_private_key'],
				'isSandbox'             => 'yes' === $this->configs->settings['sandbox_mode_enabled'],
				'baseCurrency'          => strtoupper( get_woocommerce_currency() ),
				'notificationUrl'       => esc_url( home_url( '/' ) ),
				'redirectUrl'           => esc_url( home_url( '/' ) ),
				'userValues'            => [
					1 => 'from_woocommerce',
					3 => 'version=' . WC_EBANX::get_plugin_version(),
				],
			)
		);
	}

	/**
	 *
	 * @return \Ebanx\Benjamin\Facade
	 */
	public function ebanx() {
		return $this->ebanx;
	}
}
