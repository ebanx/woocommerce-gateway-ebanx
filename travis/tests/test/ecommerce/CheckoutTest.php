<?php

namespace Ebanx\Woocommerce\Test\Ecommerce;

use Ebanx\Woocommerce\Test\BaseTest;
use Ebanx\Woocommerce\Operator\Ecommerce\EcommerceOperator;

class CartTest extends  BaseTest {
    protected $mock = array(
        product => 'Acme'
    );

    public function testBuyAcmeProduct() {
        $ecommerceOperator = new EcommerceOperator($this);
        $ecommerceOperator->checkoutProduct($this->mock[product]);
    }
}