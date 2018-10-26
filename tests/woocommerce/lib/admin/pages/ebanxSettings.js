/* global Cypress */

const togglePaymentOption = Symbol('togglePaymentOption');

export default class EbanxSettings {
  constructor(cy) {
    this.cy = cy;
  }

  toggleManualReviewOption() {
    this[togglePaymentOption]('#woocommerce_ebanx-global_manual_review_enabled');

    return this;
  }

  toggleCaptureOption() {
    this[togglePaymentOption]('#woocommerce_ebanx-global_capture_enabled');

    return this;
  }

  visit() {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin/admin.php?page=wc-settings&tab=checkout&section=ebanx-global`);

    return this;
  }

  [togglePaymentOption](elm) {
    this.cy
      .get('body')
      .then(($body) => {
        const closedOptions = $body.find('#woocommerce_ebanx-global_payments_options_title.closed');
        if (closedOptions && closedOptions.length) {
          this.cy
            .get('#woocommerce_ebanx-global_payments_options_title', {timeout: 30000})
            .should('be.visible')
            .click();
        }
      });

    this.cy
      .get(elm, { timeout: 30000 })
      .should('be.visible')
      .click()
      .get('#mainform > p.submit > button', { timeout: 30000 })
      .should('be.visible')
      .click();
  }
}
