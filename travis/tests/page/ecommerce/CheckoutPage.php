<?php

namespace Ebanx\Woocommerce\Page\Ecommerce;

use Ebanx\Woocommerce\Page\BasePage;
use EBANX\Woocommerce\Utils\StaticData;

class CheckoutPage extends BasePage {
    public function assertSingleProductPresent($product) {
        $this->baseTest->assertRegExp("/^$product/", $this->baseTest->byCssSelector('td.product-name')->text());

        return $this;
    }

    public function fillCustomerData(array $customerData) {
        $this->postCodeField()->value($customerData[postcode]);
        $this->firstNameField()->value($customerData[firstName]);
        $this->lastNameField()->value($customerData[lastName]);
        $this->emailField()->value($customerData[email]);
        $this->phoneField()->value($customerData[phone]);

        $this->countrySelectFill($customerData[country]);

        $this->baseTest->waitUntil(function() use ($customerData) {
            return $this->countrySelect()->value()===array_flip(StaticData::$COUNTRIES)[$customerData[country]];
        }, 3000);

        $this->addressField()->value($customerData[address]);
        $this->cityField()->value($customerData[city]);

        $this->stateSelectFill($customerData[state]);

        $this->baseTest->waitUntil(function() use ($customerData) {
            return $this->stateSelect()->value() === $customerData[state];
        }, 3000);

        return $this;
    }

    public function assertCheckoutPaidSuccess() {
        $this->baseTest->waitUntil(function() {
            return $this->orderReceivedMessage()->displayed() && $this->orderReceivedMessage()->text() === "Order Received";
        }, 5000);

        return $this;
    }

    public function choosePaymentMethod($method) {
        /*
            TODO: The best way to wait Ajax
            $this->baseTest->waitUntil(function() use ($method) {
                $this->paymentMethodRadio($method)->enabled();
            }, 3000);
        */

        sleep(2); // TODO: The best way to wait Ajax

        $this->paymentMethodRadio($method)->click();
        $this->paymentMethodRadio($method)->click();

        return $this;
    }

    public function fillBankingTicket() {
        $this->choosePaymentMethod('banking-ticket');

        return $this;
    }

    public function fillCreditCard($data) {
        $this->choosePaymentMethod('credit-card');

        $creditCardFields = array(
            cvc        => $this->baseTest->byCssSelector("#ebanx-card-cvc"),
            number     => $this->baseTest->byCssSelector("#ebanx-card-number"),
            expiry     => $this->baseTest->byCssSelector("#ebanx-card-expiry"),
            holderName => $this->baseTest->byCssSelector("#ebanx-card-holder-name")
        );

        $creditCardFields[holderName]->value($data[holderName]);
        $creditCardFields[number]->value($data[number]);
        $creditCardFields[expiry]->value($data[expiry]);
        $creditCardFields[cvc]->value($data[cvc]);

        return $this;
    }

    public function placeOrder() {
        $this->placeOrderButton()->click();

        return $this;
    }

    private function orderReceivedMessage() {
        return $this->baseTest->byCssSelector(".entry-title");
    }

    private function paymentMethodRadio($method) {
        return $this->baseTest->byCssSelector("#payment_method_ebanx-$method");
    }

    private function placeOrderButton() {
        return $this->baseTest->byCssSelector("#place_order");
    }

    private function firstNameField() {
        return $this->baseTest->byCssSelector("#billing_first_name");
    }

    private function lastNameField() {
        return $this->baseTest->byCssSelector("#billing_last_name");
    }

    private function emailField() {
        return $this->baseTest->byCssSelector("#billing_email");
    }

    private function phoneField() {
        return $this->baseTest->byCssSelector("#billing_phone");
    }

    private function countrySelectFill($country) {
        $this->baseTest->execute(array(
            script => "
                jQuery(\"#s2id_billing_country>a.select2-choice\").trigger(\"mousedown\");
                jQuery('.select2-result-label:contains(\"$country\")').trigger(\"mouseup\");
            ",
            args => array()
        ));
    }

    private function stateSelectFill($state) {
        $this->baseTest->execute(array(
            script => "
                jQuery(\"#s2id_billing_state>a.select2-choice\").trigger(\"mousedown\");
                jQuery('.select2-result-label:contains(\"$state\")').trigger(\"mouseup\");
            ",
            args => array()
        ));
    }

    private function addressField() {
        return $this->baseTest->byCssSelector("#billing_address_1");
    }

    private function countrySelect() {
        return $this->baseTest->byCssSelector("#billing_country");
    }

    private function cityField() {
        return $this->baseTest->byCssSelector("#billing_city");
    }

    private function stateSelect() {
        return $this->baseTest->byCssSelector("#billing_state");
    }

    private function postCodeField() {
        return $this->baseTest->byCssSelector("#billing_postcode");
    }
}