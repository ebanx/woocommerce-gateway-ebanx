<?php

namespace Ebanx\Woocommerce\Test\Ecommerce;

use Ebanx\Woocommerce\Test\BaseTest;

use Ebanx\Woocommerce\Utils\StaticData;

class CheckoutTest extends  BaseTest {
    protected $mock = array(
        admin => array(),
        ecommerce => array(
            product => 'Acme'
        )
    );

    protected function setUp() {
        parent::setUp();

        $this->mock[admin][user] = array (
            login    => StaticData::$ADMIN[USER][LOGIN],
            password => StaticData::$ADMIN[USER][PASSWORD]
        );

        $this->mock[ecommerce][checkoutData][payment_data] = array (
            cvv        => StaticData::$CARD[CVV],
            expiry     => StaticData::$CARD[EXPIRY],
            number     => StaticData::$CARD[NUMBER],
            holderName => $this->faker->name
        );

        $this->mock[ecommerce][checkoutData][customer] = array (
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

        // echo json_encode($this->mock);
    }

    public function testBuyProductPayWithCreditCard() {
        $this->ecommerceOperator->buyProductPayWithCreditCard($this->mock[ecommerce][product], $this->mock[ecommerce][checkoutData]);
    }

    public function testPlaceOrderCreditCard() {
        $this->adminOperator
            ->login($this->mock[admin][user])
        ;

        $this->ecommerceOperator
            ->buyProductPayWithCreditCard(
                $this->mock[ecommerce][product],
                $this->mock[ecommerce][checkoutData]
            )
        ;

        $mock = $this->mock;

        $mock[ecommerce][checkoutData][payment_data][brand] = StaticData::$CARD[BRAND];
        $mock[ecommerce][checkoutData][payment_data][maskedNumber] = StaticData::$CARD[MASKED_NUMBER];

        $this->ecommerceOperator
            ->buyProductPayWithExistingCreditCard(
                $mock[ecommerce][product],
                $mock[ecommerce][checkoutData]
            )
        ;

        unset($mock);
    }

    public function testBuyProductPayWithBankingTicket() {
        $this->ecommerceOperator->buyProductPayWithBankingTicket($this->mock[ecommerce][product], $this->mock[ecommerce][checkoutData]);
    }
}
