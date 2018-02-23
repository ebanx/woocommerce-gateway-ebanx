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
    country: 'Ecuador',
    countryId: 'EC',
  }
));

let woocommerce;

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    woocommerce = new Woocommerce(cy);
  });

  context('Ecuador', () => {
    context('SafetyPay', () => {
      it('can buy `wonder womans purse` using SafetyPay(CASH) to personal', () => {
        woocommerce.buyWonderWomansPurseWithSafetyPayToPersonal(mock(
          {
            paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ec.safetyPay.types.cash,
          }
        ));
      });

      it('can buy `wonder womans purse` using SafetyPay(ONLINE) to personal', () => {
        woocommerce.buyWonderWomansPurseWithSafetyPayToPersonal(mock(
          {
            paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ec.safetyPay.types.online,
          }
        ));
      });
    });
  });
});
