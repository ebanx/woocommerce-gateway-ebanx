/* global Cypress */
import AddOrder from '../pages/addOrder';

const visitNewOrderPage = Symbol('visitNewOrderPage');

export default class Admin {
  constructor(cy) {
    this.cy = cy;
    this.pages = {
      newOrder: new AddOrder(cy),
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

  logout() {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin`)
      .get('#wp-admin-bar-logout > a:nth-child(1)')
      .click({ force: true })
      .get('.message')
      .should('be.visible')
      .contains('You are now logged out.');
  }

  buyJeans(country, next) {
    this[visitNewOrderPage]();

    this.pages.newOrder.placeWithPaymentByLink(country, next);
  }

  notifyPayment(hash) {
    this.cy.request('GET', `${Cypress.env('DEMO_URL')}/?operation=payment_status_change&notification_type=update&hash_codes=${hash}`);
  }

  [visitNewOrderPage] () {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post-new.php?post_type=shop_order`)
      .get('.button.add-line-item', { timeout: 30000 })
      .should('be.visible')
      .click();
  }
}
