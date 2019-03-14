/* global Cypress, it, describe, before, context, cy */

import Faker from 'faker';
import defaults from '../../../../defaults';
import Woocommerce from '../../../lib/operator';

describe('Woocommerce', () => {
  context('Argentina', () => {
    context('Credit Card', () => {
      it('can buy `wonder womans purse` using credit card', () => {
        cy.visit(Cypress.env('DEMO_URL') + '/jeans');

        cy.get('#primary form.cart button.single_add_to_cart_button').should('be.visible');
        cy.get('#primary form.cart button.single_add_to_cart_button').click();

        cy.get('#content div.woocommerce-message a.button').should('be.visible');
        cy.get('#content div.woocommerce-message a.button').click();

        cy.get('#main div.cart-collaterals a.checkout-button').should('be.visible');
        cy.get('#main div.cart-collaterals a.checkout-button').click();

        cy.get('#billing_first_name').type('MESSI');
        cy.get('#billing_last_name').type('LIONEL ANDRES');
        cy.get('#billing_country').should('be.visible').window().then((win) => {
          win.jQuery('#billing_country').select2('open');
        }).contains('.select2-results__option', 'Argentina').trigger('mouseup');
        cy.get('#billing_address_1').type(Faker.address.streetName());
        cy.get('#billing_city').type(Faker.address.city());
        cy.get('#billing_state').should('be.visible').window().then((win) => {
          win.jQuery('#billing_state').select2('open');
        }).contains('.select2-results__option', 'Catamarca').trigger('mouseup');
        cy.get('#billing_postcode').type(Faker.address.zipCode());
        cy.get('#billing_phone').type(Faker.phone.phoneNumberFormat(2));
        cy.get('#billing_email').type(Faker.internet.email());

        cy.get('#payment li.payment_method_ebanx-credit-card-ar').should('be.visible');
        cy.get('#payment_method_ebanx-credit-card-ar').check({force: true});
        cy.get('#ebanx-card-number').type(defaults._globals.cardsWhitelist.visa);
        cy.get('#ebanx-card-expiry').type('12/99');
        cy.get('#ebanx-card-cvv').type('123');

        cy.get('#ebanx_billing_argentina_document_type').should('be.visible').window().then((win) => {
          win.jQuery('#ebanx_billing_argentina_document_type').select2('open');
        }).contains('.select2-results__option', 'CUIT').trigger('mouseup');
        cy.get('#ebanx_billing_argentina_document').type('23-33016244-9');

        cy.get('#place_order').click();

        cy.get('#primary div.woocommerce-order')
          .should('be.visible');
      });
    });

    context('Errors', () => {
      it('Must have show error when using a CUIT invalid', () => {
        cy.visit(Cypress.env('DEMO_URL') + '/jeans');

        cy.get('#primary form.cart button.single_add_to_cart_button').should('be.visible');
        cy.get('#primary form.cart button.single_add_to_cart_button').click();

        cy.get('#content div.woocommerce-message a.button').should('be.visible');
        cy.get('#content div.woocommerce-message a.button').click();

        cy.get('#main div.cart-collaterals a.checkout-button').should('be.visible');
        cy.get('#main div.cart-collaterals a.checkout-button').click();

        cy.get('#billing_first_name').type('MESSI');
        cy.get('#billing_last_name').type('LIONEL ANDRES');
        cy.get('#billing_country').should('be.visible').window().then((win) => {
          win.jQuery('#billing_country').select2('open');
        }).contains('.select2-results__option', 'Argentina').trigger('mouseup');
        cy.get('#billing_address_1').type(Faker.address.streetName());
        cy.get('#billing_city').type(Faker.address.city());
        cy.get('#billing_state').should('be.visible').window().then((win) => {
          win.jQuery('#billing_state').select2('open');
        }).contains('.select2-results__option', 'Catamarca').trigger('mouseup');
        cy.get('#billing_postcode').type(Faker.address.zipCode());
        cy.get('#billing_phone').type(Faker.phone.phoneNumberFormat(2));
        cy.get('#billing_email').type(Faker.internet.email());

        cy.get('#payment li.payment_method_ebanx-credit-card-ar').should('be.visible');
        cy.get('#payment_method_ebanx-credit-card-ar').check({force: true});
        cy.get('#ebanx-card-number').type(defaults._globals.cardsWhitelist.visa);
        cy.get('#ebanx-card-expiry').type('12/99');
        cy.get('#ebanx-card-cvv').type('123');

        cy.get('#ebanx_billing_argentina_document_type').should('be.visible').window().then((win) => {
          win.jQuery('#ebanx_billing_argentina_document_type').select2('open');
        }).contains('.select2-results__option', 'CUIT').trigger('mouseup');
        cy.get('#ebanx_billing_argentina_document').type('23-666');

        cy.get('#place_order').click();

        cy.get('#primary div.entry-content div.woocommerce-NoticeGroup').should('be.visible');
      })

      it('Must have show error when using a DNI invalid', () => {
        cy.visit(Cypress.env('DEMO_URL') + '/jeans');

        cy.get('#primary form.cart button.single_add_to_cart_button').should('be.visible');
        cy.get('#primary form.cart button.single_add_to_cart_button').click();

        cy.get('#content div.woocommerce-message a.button').should('be.visible');
        cy.get('#content div.woocommerce-message a.button').click();

        cy.get('#main div.cart-collaterals a.checkout-button').should('be.visible');
        cy.get('#main div.cart-collaterals a.checkout-button').click();

        cy.get('#billing_first_name').type('MESSI');
        cy.get('#billing_last_name').type('LIONEL ANDRES');
        cy.get('#billing_country').should('be.visible').window().then((win) => {
          win.jQuery('#billing_country').select2('open');
        }).contains('.select2-results__option', 'Argentina').trigger('mouseup');
        cy.get('#billing_address_1').type(Faker.address.streetName());
        cy.get('#billing_city').type(Faker.address.city());
        cy.get('#billing_state').should('be.visible').window().then((win) => {
          win.jQuery('#billing_state').select2('open');
        }).contains('.select2-results__option', 'Catamarca').trigger('mouseup');
        cy.get('#billing_postcode').type(Faker.address.zipCode());
        cy.get('#billing_phone').type(Faker.phone.phoneNumberFormat(2));
        cy.get('#billing_email').type(Faker.internet.email());

        cy.get('#payment li.payment_method_ebanx-credit-card-ar').should('be.visible');
        cy.get('#payment_method_ebanx-credit-card-ar').check({force: true});
        cy.get('#ebanx-card-number').type(defaults._globals.cardsWhitelist.visa);
        cy.get('#ebanx-card-expiry').type('12/99');
        cy.get('#ebanx-card-cvv').type('123');

        cy.get('#ebanx_billing_argentina_document_type').should('be.visible').window().then((win) => {
          win.jQuery('#ebanx_billing_argentina_document_type').select2('open');
        }).contains('.select2-results__option', 'DNI').trigger('mouseup');
        cy.get('#ebanx_billing_argentina_document').type('1234');

        cy.get('#place_order').click();

        cy.get('#primary div.entry-content div.woocommerce-NoticeGroup').should('be.visible');
      })
    });
  });
});
