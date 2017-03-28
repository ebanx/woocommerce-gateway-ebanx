describe('Boleto', () => {

  context('Payment', () => {

    it('Make a Payment', () => {
      cy.visit('http://localhost:3000');

      cy.get('.products .first > a')
        .first()
        .click();

      cy.get('.single_add_to_cart_button')
        .click();

      cy.get('.woocommerce-message')
        .find('.button')
        .click();

      cy.get('.checkout-button')
        .click();

      cy
        .get('#billing_first_name').type('Cezar Luiz')
        .get('#billing_last_name').type('Sampaio')
        .get('#billing_email').type('cezar@ebanx.com')
        .get('#billing_phone').type('41 99999-9999')
        .get('#billing_country_field > div.country_select').click()
          .find('.select2-result').contains('Brazil').click()
        .get('#billing_address_1').type('Rua Candido Xavier 1426')
        .get('#billing_city').type('Curitiba')
        .get('#billing_state_field > div.state_select').click()
          .contains('Paraná').click()
        .get('#ebanx_billing_brazil_document').type('07834442902')
        .get('#ebanx_billing_brazil_birth_date').type('30/11/1992')
        .get('label[for="payment_method_ebanx-banking-ticket"]').click()
        .get('#place_order').click();


    });

  });

});