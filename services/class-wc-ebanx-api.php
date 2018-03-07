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
	public function __construct(WC_EBANX_Global_Gateway $configs)
	{
		$this->configs = $configs;
		$this->ebanx = EBANX($this->getConfig());
	}



	public function getConfig()
	{
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

//	/**
//	 * @return CreditCardConfig
//	 */
//	private function getCreditCardConfig()
//	{
//		$creditCardConfig = new CreditCardConfig(array(
//			'maxInstalments' => Mage::helper('ebanx')->getMaxInstalments(),
//			'minInstalmentAmount' => Mage::helper('ebanx')->getMinInstalmentValue(),
//		));
//
//		return $creditCardConfig;
//	}

	public function ebanx()
	{
		return $this->ebanx;
	}

	public function ebanxCreditCard()
	{
		return $this->ebanx;
	}
}
