<?php

namespace Ebanx\Woocommerce\Operator;

class BaseOperator {
    protected $baseTest;
    public function __construct(\PHPUnit_Extensions_Selenium2TestCase $baseTest) {
        $this->baseTest = $baseTest;
    }
}