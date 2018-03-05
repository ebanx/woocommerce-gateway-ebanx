/* global expect */

const stillOn = Symbol('stillOn');

export default class ThankYou {
  constructor(cy) {
    this.cy = cy;
  }

  [stillOn] (method) {
    this.cy
      .get('.woocommerce-order-overview.woocommerce-thankyou-order-details.order_details', { timeout: 15000 })
      .should('be.visible')
      .contains('.woocommerce-order-overview__payment-method.method', method)
      .should('be.visible')
    ;
  }

  stillOnBoleto() {
    this.cy
      .get('#ebanx-boleto-frame', { timeout: 15000 })
      .should('be.visible')
    ;

    return this;
  }

  stillOnCreditCard() {
    this[stillOn](/(Crédito)/);

    return this;
  }

  stillOnDebitCard() {
    this[stillOn](/(Débito)/);

    return this;
  }

  stillOnSpei() {
    this[stillOn]('SPEI');

    this.cy
      .get('#post-5 > div > div > div > section.woocommerce-order-details > div:nth-child(7) > iframe')
      .then(($oxxoIframe) => {
        expect($oxxoIframe.contents().find('table.spei-table.non-responsive .amount').length).to.equal(2);
      });

    return this;
  }

  stillOnBaloto() {
    this[stillOn]('Baloto');

    this.cy
      .get('#post-5 > div > div > div > section.woocommerce-order-details > div:nth-child(7) > iframe')
      .then(($oxxoIframe) => {
        expect($oxxoIframe.contents().find('.baloto-details__item .affiliation_code').length).to.equal(1);
      });

    return this;
  }

  stillOnOxxo() {
    this[stillOn]('OXXO');

    this.cy
      .get('#post-5 > div > div > div > section.woocommerce-order-details > div:nth-child(7) > iframe')
      .then(($oxxoIframe) => {
        expect($oxxoIframe.contents().find('div.oxxo-barcode > div.oxxo-barcode-img').length).to.equal(1);
      });

    return this;
  }

  stillOnPagoEfectivo() {
    this[stillOn]('PagoEfectivo');

    this.cy
      .get('#post-5 > div > div > div > section > div:nth-child(6) > iframe')
      .then(($pagoEfectivoIframe) => {
        expect($pagoEfectivoIframe.contents().find('.cip-code').length).to.equal(1);
      });

    return this;
  }

  stillOnEfectivo() {
    this[stillOn]('Efectivo');

    this.cy
      .get('#post-5 > div > div > div > section.woocommerce-order-details > div:nth-child(7) > iframe')
      .then(($efectivoIframe) => {
        expect($efectivoIframe.contents().find('.barcode.img-responsive').length).to.equal(1);
      });
  }
}
