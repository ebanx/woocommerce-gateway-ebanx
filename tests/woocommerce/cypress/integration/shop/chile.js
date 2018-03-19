/* global Cypress, it, describe, before, context, cy */

import R from 'ramda';
import Faker from 'faker';
import defaults from '../../../defaults';
import { assertUrlStatus } from '../../../utils';
import Woocommerce from '../../lib/operator';

Faker.locale = 'es';

const mock = (data) => (R.merge(
  data,
  {
    firstName: Faker.name.firstName(),
    lastName: Faker.name.lastName(),
    address: Faker.address.streetName(),
    city: Faker.address.city(),
    state: Faker.address.state(),
    zipcode: Faker.address.zipCode(),
    phone: Faker.phone.phoneNumberFormat(2),
    email: Faker.internet.email(),
    country: 'Chile',
    countryId: 'CL',
  }
));

let woocommerce;

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    woocommerce = new Woocommerce(cy);
  });

  context('Chile', () => {
    context('Sencillito', () => {
      it('can buy `wonder womans purse` using Sencillito to personal', () => {
        woocommerce.buyWonderWomansPurseWithSencillitoToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.cl.sencillito.id,
          }
        ));
      });
    });

    context('ServiPag', () => {
      it('can buy `wonder womans purse` using ServiPag to personal', () => {
        woocommerce.buyWonderWomansPurseWithServiPagToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.cl.servipag.id,
          }
        ));
      });
    });

    context('Webpay', () => {
      it('can buy `wonder womans purse` using Webpay to personal', () => {
        woocommerce.buyWonderWomansPurseWithWebpayToPersonal(mock(
          {
            document: Faker.random.uuid(),
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.cl.webpay.id,
          }
        ));
      });
    });

    context('Multicaja', () => {
      it('can buy `wonder womans purse` using Multicaja to personal', () => {
        woocommerce.buyWonderWomansPurseWithMulticajaToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.cl.multicaja.id,
          }
        ));
      });
    });
  });
});
