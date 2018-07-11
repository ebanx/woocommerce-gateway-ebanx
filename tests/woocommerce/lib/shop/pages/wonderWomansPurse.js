/* global Cypress */

import R from 'ramda';

const payNow = Symbol('payNow');
const onClick = Symbol('onClick');
const fillCvv = Symbol('fillCvv');
const viewCart = Symbol('viewCart');
const addToCart = Symbol('addToCart');
const fillInput = Symbol('fillInput');
const clickElement = Symbol('clickElement');
const proceedTocheckout = Symbol('proceedTocheckout');

export default class WonderWomansPurse {
  constructor(cy) {
    this.cy = cy;
  }

  [addToCart] () {
    return this.cy
      .get('.single_add_to_cart_button.button.alt')
      .should('be.visible');
  }

  [viewCart] () {
    return this.cy
      .get('.button.wc-forward', { timeout: 10000 })
      .should('be.visible');
  }

  [proceedTocheckout] () {
    return this.cy
      .get('.checkout-button.button.alt.wc-forward', { timeout: 10000 })
      .should('be.visible');
  }

  [clickElement] (element) {
    this.cy
      .get(element, { timeout: 10000 })
      .should('be.visible')
      .click();
  }

  [onClick] () {
    this[clickElement]('#ebanx-one-click-button');
  }

  [fillInput] (data, property, input) {
    R.ifElse(
      R.propSatisfies((x) => (x !== undefined), property), (data) => {
        this.cy
          .get(input, { timeout: 10000 })
          .should('be.visible')
          .clear()
          .type(data[property])
          .should('have.value', data[property]);
      },
      R.always(null)
    )(data);
  }

  [fillCvv] (cvv) {
    this[fillInput]({ cvv }, 'cvv', '#ebanx-one-click-cvv-input');
  }

  [payNow] () {
    this[clickElement]('.single_add_to_cart_button.ebanx-one-click-pay.button');
  }

  visit() {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/jeans/`);

    this[addToCart]();

    return this;
  }

  buy() {
    this.visit();

    this[addToCart]().click();
    this[viewCart]().click();
    this[proceedTocheckout]();

    return this;
  }

  buyByOneClick(cvv) {
    this.visit();

    this[onClick]();
    this[fillCvv](cvv);
    this[payNow]();

    return this;
  }
}
