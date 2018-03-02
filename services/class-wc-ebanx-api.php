<?php
require_once WC_EBANX_VENDOR_DIR . '/autoload.php';
require_once WC_EBANX_DIR . 'woocommerce-gateway-ebanx.php';

use Ebanx\Benjamin\Models\Configs\Config;
use Ebanx\Benjamin\Models\Configs\CreditCardConfig;

class EBANX_Api
{
	protected $ebanx;
	protected $configs;

	/**
	 * EBANX_Api constructor.
	 */
	public function __construct(WC_EBANX_Global_Gateway $configs)
	{
		$this->configs = $configs;
		$this->ebanx = EBANX($this->getConfig());
	}



	public function getConfig()
	{
		$is_sandbox_mode = 'yes' === $this->configs->settings['sandbox_mode_enabled'];
		$private_key = $is_sandbox_mode ? $this->configs->settings['sandbox_private_key'] : $this->configs->settings['live_private_key'];
		$public_key = $is_sandbox_mode ? $this->configs->settings['sandbox_public_key'] : $this->configs->settings['live_public_key'];

		return new Config(array(
			'integrationKey' => $private_key,
			'sandboxIntegrationKey' => $public_key,
			'isSandbox' => $is_sandbox_mode,
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
