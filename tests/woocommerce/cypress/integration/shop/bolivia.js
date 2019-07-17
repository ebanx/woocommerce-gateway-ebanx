/* global Cypress, it, describe, before, context, cy */

import R from 'ramda';
import Faker from 'faker';
import defaults from '../../../../defaults';
import {assertUrlStatus} from '../../../../utils';
import Woocommerce from '../../../lib/operator';
import Pay from "../../../../pay/lib/operator";

Faker.locale = 'es';

const mock = (data) => (R.merge(
  data,
  {
    firstName: Faker.name.firstName(),
    lastName: Faker.name.lastName(),
    address: Faker.address.streetName(),
    state: 'Beni',
    stateId: 'H',
    country: 'Bolivia',
    countryId: 'BO',    city: Faker.address.city(),
    phone: Faker.phone.phoneNumberFormat(2),
    email: Faker.internet.email(),
  }
));

let woocommerce;
let pay;

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    pay = new Pay(cy);
    woocommerce = new Woocommerce(cy);
  });

  context('Bolivia', () => {
    context('Pagosnet', () => {
      it('can buy `wonder womans purse` using Pagosnet', () => {
        woocommerce.buyWonderWomansPurseWithPagosnet(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.bo.pagosnet.id,
          }
        ));
      });
    });
  });
});
