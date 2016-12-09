<?php

namespace Ebanx\Woocommerce\Test\Ecommerce;

use Ebanx\Woocommerce\Test\BaseTest;

use Ebanx\Woocommerce\Operator\Ecommerce\EcommerceOperator;

use Ebanx\Woocommerce\Utils\StaticData;

class CheckoutTest extends  BaseTest {
    protected $mock = array(
        product => 'Acme'
    );

    protected function setUp() {
        parent::setUp();

        $this->mock[checkoutData][payment_data] = array (
            cvv        => 123,
            expiry     => "12 / 2222",
            number     => 4111111111111111,
            holderName => $this->faker->name
        );

        $this->mock[checkoutData][customer] = array (
            city      => StaticData::$CITIES[BR][PR][CTBA],
            email     => $this->faker->email,
            state     => StaticData::$STATES[BR][PR],
            phone     => $this->faker->phoneNumber,
            address   => $this->faker->streetAddress,
            document  => $this->faker->cpf,
            postcode  => $this->faker->postcode,
            lastName  => $this->faker->lastName,
            firstName => $this->faker->firstName,
            birthDate => $this->faker->dateTimeThisCentury->format('d/m/Y'),
            streetNumber => $this->faker->buildingNumber
        );
    }

    public function testBuyProductPayWithCreditCard() {
        $ecommerceOperator = new EcommerceOperator($this);
        $ecommerceOperator->buyProductPayWithCreditCard($this->mock[product], $this->mock[checkoutData]);
    }

    public function testBuyProductPayWithBankingTicket() {
        $ecommerceOperator = new EcommerceOperator($this);
        $ecommerceOperator->buyProductPayWithBankingTicket($this->mock[product], $this->mock[checkoutData]);
    }
}
