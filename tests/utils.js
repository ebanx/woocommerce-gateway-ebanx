/* global cy */

import R from 'ramda';
import Joi from 'joi';

export const wrapOrderAssertations = (payment, checkoutPayment, checkoutCustomer) => {
  wrapAssertation(
    payment,
    checkoutPayment,
    R.keys(checkoutPayment)
  );

  wrapAssertation(
    payment.customer,
    checkoutCustomer,
    R.keys(checkoutCustomer)
  );
};

export const composeName = (data) => R.pipe(
  R.pick(['firstName', 'lastName']),
  R.values,
  R.join(' '),
  R.toUpper
)(data).normalize('NFD').replace(/[\u0300-\u036f]/g, '');

export const tryNext = (next, resp = {}) => R.ifElse(
  R.propSatisfies((x) => (x instanceof Function), 'next'), (data) => {
    data.next(resp);
  },
  R.always(null)
)({ next });

export const wrapAssertation = (object, reflection, properties) => properties.map((p) => {
  cy
    .wrap(object)
    .should('have.property', p)
    .and('eq', reflection[p]);
});

export const sanitizeMethod = (method) => R.compose(
  R.toLower,
  R.replace(/ /, '')
)(method);

export const forceClearCookie = (cookieName) => {
  cy.document().then((document) => {
    document.cookie = `${cookieName}=; expires=${+new Date()}; domain=localhost; path=/`; // eslint-disable-line
    document.cookie = `${cookieName}=; expires=${+new Date()}; domain=ebanxdemo.com; path=/`; // eslint-disable-line
  });
};

export const waitUrlHas = (value, attempts = 0) => { // eslint-disable-line
  cy
    .url()
    .wait(2000)
    .then(($url) => {
      if (attempts === 100) throw new Error(`${$url} doesn't contain ${value}`);

      if ($url.indexOf(value) === -1) {
        attempts++; // eslint-disable-line
        waitUrlHas(value, attempts);
      }
    });
};

export const assertUrlStatus = (url, status = 200) => {
  cy.request(url).then((resp) => {
    // TODO: throw error ?
    expect(resp.status).to.eq(status);// eslint-disable-line
  });
};

export const validateSchema = (schema, data, next) => {
  Joi.validate(data, schema, (err) => {
    if (err) {
      throw new Error(`Invalid data to schema ${schema._inner.children[0].schema._valids._set[0]}. DETAILS: ${JSON.stringify(err.details)}`); // eslint-disable-line
    }

    next();
  });
};
