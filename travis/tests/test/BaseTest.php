<?php

namespace Ebanx\Woocommerce\Test;

use Faker;

class BaseTest extends \PHPUnit_Extensions_Selenium2TestCase {
    protected $faker;

    protected function setUp() {
        $this->faker = Faker\Factory::create('pt_BR');

        $this->setHost('localhost');
        $this->setPort(4444);
        $this->setBrowserUrl('http://127.0.0.1/');
        $this->setBrowser('firefox');
    }
    public function tearDown() {
        $this->stop();
    }
}