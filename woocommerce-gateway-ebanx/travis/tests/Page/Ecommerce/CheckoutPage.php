<?php

namespace Ebanx\Woocommerce\Page\Ecommerce;

use Ebanx\Woocommerce\Page\BasePage;
use Ebanx\Woocommerce\Utils\StaticData;

class CheckoutPage extends BasePage {
    public function assertSingleProductPresent($product) {
        $this->baseTest->assertRegExp("/^$product/", $this->baseTest->byCssSelector('td.product-name')->text());

        return $this;
    }

    public function fillCustomerDataToBrazil(array $customerData) {
        $customerData[country] = StaticData::$COUNTRIES[BR];

        $this->fillCustomerData($customerData);

        $this->documentField()->clear();
        $this->documentField()->value($customerData[document]);
        $this->birthDateField()->clear();
        $this->birthDateField()->value($customerData[birthDate]);
        $this->streeNumberField()->clear();
        $this->streeNumberField()->value($customerData[streetNumber]);

        return $this;
    }

    public function fillCustomerData(array $customerData) {
        $this->postCodeField()->clear();
        $this->postCodeField()->value($customerData[postcode]);
        $this->firstNameField()->clear();
        $this->firstNameField()->value($customerData[firstName]);
        $this->lastNameField()->clear();
        $this->lastNameField()->value($customerData[lastName]);
        $this->emailField()->clear();
        $this->emailField()->value($customerData[email]);
        $this->phoneField()->clear();
        $this->phoneField()->value($customerData[phone]);

        $this->countrySelectFill($customerData[country]);

        $country = $this->baseTest->waitUntil(function() use ($customerData) {
            return $this->countrySelect()->value() === array_flip(StaticData::$COUNTRIES)[$customerData[country]];
        }, 3000);

        $this->baseTest->assertEquals(true, $country);

        $this->baseTest->execute(array(
            script => "jQuery('#billing_address_1').val('".trim(str_replace(PHP_EOL, ' ', $customerData[address]))."');",
            args => array()
        ));

        $this->cityField()->clear();
        $this->cityField()->value($customerData[city]);

        $this->stateSelectFill($customerData[state]);

        $state = $this->baseTest->waitUntil(function() use ($customerData) {
            return $this->stateSelect()->value() === array_flip(StaticData::$STATES[array_flip(StaticData::$COUNTRIES)[$customerData[country]]])[$customerData[state]];
        }, 3000);

        $this->baseTest->assertEquals(true, $state);

        return $this;
    }

    public function assertCheckoutPaidSuccess() {
        sleep(15);

        $this->baseTest->assertEquals(
            true,
            (boolean) ($this->orderReceivedMessage()->displayed() && strtoupper($this->orderReceivedMessage()->text()) === "ORDER RECEIVED"),
            json_encode($this->checkoutErrors())
        );
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

    public function fillExistingCreditCard($data) {
        $this->existingCard($data)->click();
        $this->existingCardCvvFill($data[cvv]);

        return $this;
    }

    public function fillCreditCard($data) {
        $this->choosePaymentMethod('credit-card');

        $creditCardFields = array(
            cvv        => $this->baseTest->byCssSelector("#ebanx-card-cvv"),
            number     => $this->baseTest->byCssSelector("#ebanx-card-number"),
            expiry     => $this->baseTest->byCssSelector("#ebanx-card-expiry"),
            holderName => $this->baseTest->byCssSelector("#ebanx-card-holder-name")
        );

        $this->baseTest->execute(array(
            script => "jQuery(\"#ebanx-card-number\").val(\"$data[number]\");",
            args => array()
        ));

        $this->baseTest->execute(array(
            script => "jQuery(\"#ebanx-card-expiry\").val(\"$data[expiry]\");",
            args => array()
        ));

        $creditCardFields[holderName]->clear();
        $creditCardFields[holderName]->value($data[holderName]);
        $creditCardFields[cvv]->clear();
        $creditCardFields[cvv]->value($data[cvv]);

        return $this;
    }

    public function placeOrder() {
        $this->placeOrderButton()->click();

        return $this;
    }

    private function placeOrderButton() {
        return $this->baseTest->byCssSelector("#place_order");
    }

    private function checkoutErrors() {
        return array_map(function($element){
            return $element->text();
        }, array_merge(
            $this->errorMessagesList(),
            $this->errorMessagesParagraphs()
        ));
    }

    private function orderReceivedMessage() {
        return $this->baseTest->byCssSelector(".entry-title");
    }

    private function paymentMethodRadio($method) {
        return $this->baseTest->byCssSelector("#payment_method_ebanx-$method");
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
    
    private function streeNumberField() {
        return $this->baseTest->byCssSelector("#ebanx_billing_brazil_street_number");
    }
    
    private function birthDateField() {
        return $this->baseTest->byCssSelector("#ebanx_billing_brazil_birth_date");
    }
    
    private function documentField() {
        return $this->baseTest->byCssSelector("#ebanx_billing_brazil_document");
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

    private function existingCardCvvFill($cvv) {
        $this->baseTest->execute(array(
            script => "
                jQuery(jQuery('input[name=\"ebanx-credit-card-use\"]:checked')).parent().siblings('.ebanx-container-credit-card').find('.wc-credit-card-form-card-cvv').val($cvv);
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

    private function existingCard(array $cardData) {
        return $this->baseTest->byCssSelector(".{$cardData[brand]}-{$cardData[maskedNumber]}");
    }

    private function errorMessagesList() {
        return $this->baseTest->elements($this->baseTest->using('css selector')->value('.woocommerce-error li'));
    }

    private function errorMessagesParagraphs() {
        return $this->baseTest->elements($this->baseTest->using('css selector')->value('p.woocommerce-error'));
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