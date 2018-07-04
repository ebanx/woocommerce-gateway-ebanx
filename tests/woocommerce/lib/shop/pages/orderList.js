/* global Cypress */
export default class OrderList {
  constructor(cy) {
    this.cy = cy;
  }

  cancelPayment(orderNumber) {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/my-account/orders/`)
      .get('.woocommerce-orders-table')
      .then((elm) => {
        const orderNumberLink = elm.find(`tr > td > a:contains(${orderNumber})`);

        if (!orderNumberLink || !orderNumberLink.length) {
          throw new Error(`The link for order number #${orderNumber} wasn't found`);
        }

        return orderNumberLink.parent().parent().find('a.button.cancel');
      })
      .click()
      .get('.order-status', { timeout: 30000 })
      .should('be.visible')
      .should('contain', 'Cancelled');
  }
}
