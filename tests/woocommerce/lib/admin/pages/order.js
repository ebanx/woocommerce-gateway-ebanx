/* global Cypress */
export default class Order {
  constructor(cy) {
    this.cy = cy;
  }

  capturePayment(orderNumber) {
    this.paymentHasStatus(orderNumber, 'On hold');

    this.cy
      .get('select[name="wc_order_action"]')
      .should('be.visible')
      .select('Capture payment on EBANX')
      .should('have.value', 'ebanx_capture_order')
      .get('.save_order')
      .should('be.visible')
      .click();

    this.cy
      .get('div.notice:nth-child(4) > p:nth-child(1)', { timeout: 30000 })
      .should('contain', `Payment ${orderNumber} was captured successfully.`);
  }

  paymentHasStatus(orderNumber, status) {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post.php?post=${orderNumber}&action=edit`)
      .get('#select2-order_status-container')
      .should('contain', status);
  }
}
