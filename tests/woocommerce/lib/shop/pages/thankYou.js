/* global expect */

import { tryNext } from '../../../../utils';

const stillOn = Symbol('stillOn');
const extractHash = Symbol('extractHash');
const extractOrderNumber = Symbol('extractOrderNumber');

export default class ThankYou {
  constructor(cy) {
    this.cy = cy;
  }

  [extractHash](next) {
    this.cy
      .get('#ebanx-payment-hash')
      .then(($elm) => {
        next($elm.data('doraemon-hash'));
      });
  }

  [extractOrderNumber](next) {
    this.cy
      .get('.woocommerce-thankyou-order-details .woocommerce-order-overview__order.order > strong')
      .then(($elm) => {
        next($elm.text());
      });
  }

  [stillOn] (method) {
    this.cy
      .get('.woocommerce-order-overview.woocommerce-thankyou-order-details.order_details', { timeout: 30000 })
      .should('be.visible')
      .contains('.woocommerce-order-overview__payment-method.method', method)
      .should('be.visible')
    ;
  }

  stillOnBoleto(next) {
    this.cy
      .get('#ebanx-boleto-frame', { timeout: 15000 })
      .should('be.visible');

    this[extractHash]((hash) => {
      this[extractOrderNumber]((orderNumber) => {
        tryNext(next, { hash, orderNumber });
      });
    });
  }

  stillOnBankTransfer() {
    this.cy
      .get('#js-form', { timeout: 15000 })
      .should('be.visible');
  }

  stillOnCreditCard(instalmentNumber, next) {
    this[stillOn](/(Crédito)/);

    if(typeof instalmentNumber !== 'undefined' && instalmentNumber > 1) {
      this.cy
        .get('#ebanx-instalment-number', {timeout: 15000})
        .contains(instalmentNumber);
    }

    this[extractHash]((hash) => {
      this[extractOrderNumber]((orderNumber) => {
        tryNext(next, { hash, orderNumber });
      });
    });
  }

  stillOnDebitCard() {
    this[stillOn](/(Débito)/);

    return this;
  }

  stillOnSpei() {
    this[stillOn]('SPEI');

    this.cy
      .get('#post-6 > div > div > div > section.woocommerce-order-details > div:nth-child(7) > iframe')
      .then(($oxxoIframe) => {
        expect($oxxoIframe.contents().find('table.spei-table.non-responsive .amount').length).to.equal(2);
      });

    return this;
  }

  stillOnBaloto() {
    this[stillOn]('Baloto');

    this.cy
      .get('#post-6 > div > div > div > section.woocommerce-order-details > div:nth-child(7) > iframe')
      .then(($oxxoIframe) => {
        expect($oxxoIframe.contents().find('.voucher-generated').length).to.equal(1);
      });

    return this;
  }

  stillOnPagosnet() {
    this.cy
        .get('#pagosnet_pending', { timeout: 15000 })
        .should('be.visible');
  }

  stillOnOxxo() {
    this[stillOn]('OXXO');

    this.cy
      .get('#post-6 > div > div > div > section.woocommerce-order-details > div:nth-child(7) > iframe')
      .then(($oxxoIframe) => {
        expect($oxxoIframe.contents().find('div.oxxo-barcode > div.oxxo-barcode-img').length).to.equal(1);
      });

    return this;
  }

  stillOnPagoEfectivo() {
    this[stillOn]('PagoEfectivo');

    this.cy
      .get('#post-6 > div > div > div > section > div:nth-child(6) > iframe')
      .then(($pagoEfectivoIframe) => {
        expect($pagoEfectivoIframe.contents().find('.cip-code').length).to.equal(1);
      });

    return this;
  }

  stillOnEfectivo() {
    this[stillOn]('Efectivo');

    this.cy
      .get('#post-6 > div > div > div > section.woocommerce-order-details > div:nth-child(7) > iframe')
      .then(($efectivoIframe) => {
        expect($efectivoIframe.contents().find('.barcode.img-responsive').length).to.equal(1);
      });
  }

  stillOnPagofacilEfectivo() {
    this[stillOn]('Efectivo');

    this.cy
      .get('#post-6 > div > div > div > section.woocommerce-order-details > div:nth-child(7) > iframe')
      .then(($efectivoIframe) => {
        expect($efectivoIframe.contents().find('.voucher > .voucher_info').length).to.equal(1);
      });
  }
}
