<?php

namespace Ebanx\Woocommerce\Page;

class BasePage {
    protected $baseTest;
    public function __construct(\PHPUnit_Extensions_Selenium2TestCase $baseTest) {
        $this->baseTest = $baseTest;
    }
}