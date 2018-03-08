<?php
require_once WC_EBANX_VENDOR_DIR . '/autoload.php';
require_once WC_EBANX_DIR . 'woocommerce-gateway-ebanx.php';

use Ebanx\Benjamin\Models\Configs\Config;
use Ebanx\Benjamin\Models\Configs\CreditCardConfig;

class WC_EBANX_Api
{
	protected $ebanx;
	protected $configs;

	/**
	 * EBANX_Api constructor.
	 *
	 * @param WC_EBANX_Global_Gateway $configs
	 */
	public function __construct(WC_EBANX_Global_Gateway $configs) {
		$this->configs = $configs;
		$this->ebanx = EBANX($this->getConfig(), $this->getCreditCardConfig());
	}

	/**
	 * @return Config
	 */
	private function getConfig() {
		return new Config(array(
			'integrationKey' => $this->configs->settings['live_private_key'],
			'sandboxIntegrationKey' => $this->configs->settings['sandbox_private_key'],
			'isSandbox' => 'yes' === $this->configs->settings['sandbox_mode_enabled'],
			'baseCurrency' => strtoupper( get_woocommerce_currency() ),
			'notificationUrl' => esc_url( home_url( '/' ) ),
			'redirectUrl' => esc_url( home_url( '/' ) ),
			'userValues' => [
				1 => 'from_woocommerce',
				3 => 'version=' . WC_EBANX::get_plugin_version(),
			],
		));
	}

	/**
	 * @return CreditCardConfig
	 */
	private function getCreditCardConfig() {
		$currency_code = strtolower( get_woocommerce_currency() );

		return new CreditCardConfig(array(
			'maxInstalments' => $this->configs->settings['credit_card_instalments'],
			'minInstalmentAmount' => $this->configs->settings["min_instalment_value_$currency_code"],
		));
	}

	public function ebanx() {
		return $this->ebanx;
	}
}
