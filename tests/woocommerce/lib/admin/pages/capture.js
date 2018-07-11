/* global Cypress */
import defaults from '../../../../defaults';

export default class Capture {
  constructor(cy) {
    this.cy = cy;
  }

  request(hash) {
    this.cy.request('GET', `${defaults.pay.api.url}/capture/?integration_key=${Cypress.env('DEMO_INTEGRATION_KEY')}&hash=${hash}`);
  }
}
