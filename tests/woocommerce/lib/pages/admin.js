const fillUser = Symbol('fillUser');
const fillCountry = Symbol('fillCountry');
const addItemToOrder = Symbol('addItemToOrder');

export default class AddOrder {
  constructor(cy) {
	this.cy = cy;
  }

  placeWithPaymentByLink(country) {
    this[addItemToOrder]();
    this[fillUser]();
    this[fillCountry](country);

    return this;
  }



  [addItemToOrder] () {
    cy
      .get('.button.add-order-item', { timeout: 30000 })
      .should('be.visible')
      .click()
      .get('#wc-backbone-modal-dialog > div.wc-backbone-modal > div > section > article > form > span > span.selection > span > ul > li > input')
      .type(Cypress.env('PRODUCT_NAME'))
      .get('.select2-results__option.select2-results__option--highlighted')
      .trigger('mouseup')
      .get('#btn-ok')
      .should('be.visible')
      .click();
  }

  [fillUser] () {
    cy
      .get('#select2-customer_user-container')
      .should('be.visible')
      .click()
      .get('body > span > span > span.select2-search.select2-search--dropdown > input')
      .type(Cypress.env('ADMIN_USER'))
      .get('.select2-results__option.select2-results__option--highlighted')
      .trigger('mouseup');
  }

  [fillCountry] (country) {
    cy
      .get('select#_billing_country')
      .should('be.visible')
      .window().then((win) => {
      win.jQuery('select#_billing_country').select2('open');
    })
      .contains('.select2-results__option', country)
      .trigger('mouseup')
      .get('[name="create_ebanx_payment_link"]')
      .should('be.visible')
      .click()
      .get('#order_data > div.order_data_column_container > div:nth-child(1) > div > p:nth-child(4) > input[type="text"]', { timeout: 30000 })
      .then(($elm) => {
        console.log($elm.val());
      });
  }
}
