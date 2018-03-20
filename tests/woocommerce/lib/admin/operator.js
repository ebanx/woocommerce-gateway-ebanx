/* global Cypress */
import NewOrder from '../pages/admin';

const visitNewOrderPage = Symbol('visitNewOrderPage');

export default class Admin {
  constructor(cy) {
    this.cy = cy;
    this.pages = {
      newOrder: new NewOrder(cy),
    };
  }

  login() {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin`)
      .get('#user_login', { timeout: 30000 })
      .should('be.visible')
      .type(Cypress.env('ADMIN_USER'))
      .get('#user_pass')
      .should('be.visible')
      .type(Cypress.env('ADMIN_PASSWORD'))
      .get('#wp-submit')
      .should('be.visible')
      .click();
  }

  buyJeans(country, next) {
    this[visitNewOrderPage]();

    this.pages.newOrder.placeWithPaymentByLink(country, next);
  }

  [visitNewOrderPage] () {
    cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post-new.php?post_type=shop_order`)
      .get('.button.add-line-item', { timeout: 30000 })
      .should('be.visible')
      .click();
  }
}
