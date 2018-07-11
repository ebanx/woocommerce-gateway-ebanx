const proceedTocheckout = Symbol('proceedTocheckout');

export default class Cart {
  constructor(cy) {
    this.cy = cy;
  }

  [proceedTocheckout] () {
    return this.cy
      .get('.checkout-button.button.alt.wc-forward', { timeout: 10000 })
      .should('be.visible');
  }

  proceedToCheckoutWithOpened() {
    this[proceedTocheckout]().click();

    return this;
  }
}
