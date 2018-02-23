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
    country: 'Colombia',
    countryId: 'CO',
  }
));

let woocommerce;

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    woocommerce = new Woocommerce(cy);
  });

  context('Colombia', () => {
    context('Pse', () => {
      it('can buy `wonder womans purse` using Pse to personal', () => {
        woocommerce.buyWonderWomansPurseWithPseToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.co.pse.id,
            paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.co.pse.types.agrario,
          }
        ));
      });
    });

    context('Baloto', () => {
      it('can buy `wonder womans purse` using Baloto to personal', () => {
        woocommerce.buyWonderWomansPurseWithBalotoToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.co.baloto.id,
          }
        ));
      });
    });

    context('Credit Card', () => {
      it('can buy `wonder womans purse`, using credit card', () => {
        const mockData = {
          paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.co.creditcard.id,
          document: Faker.random.uuid(),
          card: {
            name: Faker.name.findName(),
            number: defaults._globals.cardsWhitelist.mastercard,
            expiryDate: '12/22',
            cvv: '123',
          },
        };

        woocommerce
          .buyWonderWomansPurseWithCreditCardToPersonal(mock(mockData));
      });
    });
  });
});
