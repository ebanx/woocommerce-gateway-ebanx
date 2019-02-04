<?php

use PHPUnit\Framework\TestCase;

class EbanxHelperTest extends TestCase {
	public function testPluginCheckArray() {
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
