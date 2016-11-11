<?php

namespace Ebanx\Woocommerce\Test\Ecommerce;

use Ebanx\Woocommerce\Test\BaseTest;

use Ebanx\Woocommerce\Utils\StaticData;

use Ebanx\Woocommerce\Operator\Ecommerce\EcommerceOperator;

class CheckoutTest extends  BaseTest {
    protected $mock = array(
        product => 'Acme'
    );

    protected function setUp() {
        parent::setUp();

        $this->mock[checkoutData][payment_data] = array (
            cvc        => 123,
            expiry     => 122222,
            number     => 4111111111111111,
            holderName => $this->faker->name
        );

        $this->mock[checkoutData][customer] = array (
            city      => $this->faker->city,
            email     => $this->faker->email,
            state     => 'Paraná',
            phone     => $this->faker->phoneNumber,
            address   => $this->faker->address,
            country   => StaticData::$COUNTRIES[BR],
            postcode  => $this->faker->postcode,
            lastName  => $this->faker->lastName,
            firstName => $this->faker->firstName
        );
    }

    public function testBuyProductPayWithCreditCard() {
        $ecommerceOperator = new EcommerceOperator($this);
        $ecommerceOperator->buyProductPayWithCreditCard($this->mock[product], $this->mock[checkoutData]);
    }
}