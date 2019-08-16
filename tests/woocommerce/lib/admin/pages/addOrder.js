/* global Cypress */
const fillUser = Symbol('fillUser');
const fillCountry = Symbol('fillCountry');
const addItemToOrder = Symbol('addItemToOrder');
const createPaymentByLink = Symbol('createPaymentByLink');

export default class AddOrder {
  constructor(cy) {
    this.cy = cy;
  }

  placeWithPaymentByLink(country, next) {
    this[addItemToOrder]();
    this[fillUser]();
    this[fillCountry](country);
    this[createPaymentByLink](next);
  }

  visit() {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post-new.php?post_type=shop_order`)
      .get('.button.add-line-item', { timeout: 30000 })
      .should('be.visible')
      .click();
  }

  [addItemToOrder] () {
    this.cy
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
    this.cy
      .get('#select2-customer_user-container')
      .should('be.visible')
      .click()
      .get('body > span > span > span.select2-search.select2-search--dropdown > input')
      .type(Cypress.env('ADMIN_USER'))
      .get('.select2-results__option.select2-results__option--highlighted')
      .trigger('mouseup');
  }

  [fillCountry] (country) {
    this.cy
      .get('select#_billing_country')
      .should('be.visible')
      .window().then((win) => {
        win.jQuery('select#_billing_country').select2('open');
      })
      .contains('.select2-results__option', country)
      .trigger('mouseup');
  }

  [createPaymentByLink] (next) {
    this.cy
      .get('[name="create_ebanx_payment_link"]')
      .should('be.visible')
      .click()
      .get('#order_data .order_data_column_container input[name="order_date"]', { timeout: 30000 })
      .then(($elm) => {
        next($elm.val());
      });
  }
}
