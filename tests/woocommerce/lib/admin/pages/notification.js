/* global Cypress */
export default class Notification {
  constructor(cy) {
    this.cy = cy;
  }

  send(hash) {
    this.cy.request('GET', `${Cypress.env('DEMO_URL')}/?operation=payment_status_change&notification_type=update&hash_codes=${hash}`);
  }
}
