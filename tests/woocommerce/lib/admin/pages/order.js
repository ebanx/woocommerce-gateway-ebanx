/* global Cypress */
const visit = Symbol('visit');

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
    this[visit](orderNumber);

    this.cy
      .get('#select2-order_status-container')
      .should('contain', status);
  }

  refundOrder(orderNumber) {
    this[visit](orderNumber);

    this.cy
      .get('.button.refund-items', { timeout: 30000 })
      .should('be.visible')
      .click()
      .get('.refund_order_item_qty')
      .should('be.visible')
      .type('1')
      .get('.button.button-primary.do-api-refund', { timeout: 5000 })
      .should('be.visible')
      .click()
      .get('.blockUI.blockOverlay')
      .should('be.not.visible', { timeout: 30000 })
      .get('li.note:nth-child(1) > div:nth-child(1) > p:nth-child(1)')
      .should('be.visible')
      .should('contain', 'Order status changed from Processing to Refunded.');
  }

  [visit](orderNumber) {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post.php?post=${orderNumber}&action=edit`);
  }
}
