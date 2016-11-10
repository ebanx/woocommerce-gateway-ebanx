<?php

namespace Ebanx\Woocommerce\Test;

class BaseTest extends \PHPUnit_Extensions_Selenium2TestCase {
    protected function setUp() {
        $this->setHost('localhost');
        $this->setPort(4444);
        $this->setBrowserUrl('http://127.0.0.1/');
        $this->setBrowser('firefox');
    }
    public function tearDown() {
        $this->stop();
    }
}