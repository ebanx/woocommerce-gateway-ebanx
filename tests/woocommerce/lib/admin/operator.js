/* global Cypress */
import Order from './pages/Order';
import AddOrder from './pages/addOrder';
import EbanxSettings from './pages/ebanxSettings';
import defaults from "../../../defaults";

const visitNewOrderPage = Symbol('visitNewOrderPage');

export default class Admin {
  constructor(cy) {
    this.cy = cy;
    this.pages = {
      order: new Order(cy),
      newOrder: new AddOrder(cy),
      ebanxSettings: new EbanxSettings(cy),
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

    return this;
  }

  logout() {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin`)
      .get('#wp-admin-bar-logout > a:nth-child(1)')
      .click({ force: true })
      .get('.message')
      .should('be.visible')
      .contains('You are now logged out.');

    return this;
  }

  buyJeans(country, next) {
    this[visitNewOrderPage]();

    this.pages.newOrder.placeWithPaymentByLink(country, next);
  }

  notifyPayment(hash) {
    this.cy.request('GET', `${Cypress.env('DEMO_URL')}/?operation=payment_status_change&notification_type=update&hash_codes=${hash}`);

    return this;
  }

  toggleManualReviewOption() {
    this.pages.ebanxSettings.togglePaymentOption('#woocommerce_ebanx-global_capture_enabled');

    return this;
  }

  checkPaymentStatusOnPlatform(orderNumber, status) {
    this.pages.order.paymentHasStatus(orderNumber, status);
  }

  toggleCaptureOption() {
    this.pages.ebanxSettings.togglePaymentOption('#woocommerce_ebanx-global_capture_enabled');

    return this;
  }

  captureCreditCardPayment(orderNumber) {
    this.pages.order.capturePayment(orderNumber);

    return this;
  }

  captureCreditCardPaymentThroughAPI(hash) {
    this.cy.request('GET', `${defaults.pay.api.url}/capture/?integration_key=${Cypress.env('DEMO_INTEGRATION_KEY')}&hash=${hash}`);

    return this;
  }

  [visitNewOrderPage] () {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post-new.php?post_type=shop_order`)
      .get('.button.add-line-item', { timeout: 30000 })
      .should('be.visible')
      .click();
  }
}
