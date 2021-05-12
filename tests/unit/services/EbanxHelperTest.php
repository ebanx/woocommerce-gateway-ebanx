<?php

use PHPUnit\Framework\TestCase;
use EBANX\Plugin\Services\WC_EBANX_Helper;

class EbanxHelperTest extends TestCase {
	public function setUp(): void {
		\WP_Mock::setUp();
	}
	public function testPluginCheckArray() {
		\WP_Mock::userFunction( 'get_woocommerce_currency', [
			'return_in_order' => ['BRL']
		]);
		\WP_Mock::userFunction( 'get_plugins', array(
			'return_in_order' => array([['Version' => '0.0.1', 'Name' => 'Mock']])
		));

		$config = new WC_EBANX_Global_Gateway();
		$plugin_check = WC_EBANX_Helper::plugin_check($config);
		$this->assertPluginCheck($plugin_check);
	}

	private function assertPluginCheck(array $plugin_check) {
		$this->assertArrayHasKey('php', $plugin_check);
		$this->assertArrayHasKey('mysql', $plugin_check);
		$this->assertArrayHasKey('plugins', $plugin_check);
		$this->assertArrayHasKey('configs', $plugin_check);
	}
}
