/* global Cypress, it, describe, before, context, cy */

import R from 'ramda';
import Faker from 'faker';
import defaults from '../../../defaults';
import { assertUrlStatus } from '../../../utils';
import Woocommerce from '../../lib/operator';

Faker.locale = 'es_MX';

const mock = (data) => (R.merge(
  data,
  {
    firstName: Faker.name.firstName(),
    lastName: Faker.name.lastName(),
    address: Faker.address.streetName(),
    city: Faker.address.city(),
    state: 'Ciudad de México',
    stateId: 'DF',
    zipcode: Faker.address.zipCode(),
    phone: Faker.phone.phoneNumberFormat(2),
    email: Faker.internet.email(),
    country: 'Mexico',
    countryId: 'MX',
  }
));

let woocommerce;

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    woocommerce = new Woocommerce(cy);
  });

  context('Mexico', () => {
    context('Oxxo', () => {
      it('can buy `wonder womans purse` using oxxo to personal', () => {
        woocommerce.buyWonderWomansPurseWithOxxoToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.mx.oxxo.id,
          }
        ));
      });
    });

    context('Debit Card', () => {
      it('can buy `wonder womans purse`, using debit card', () => {
        const mockData = {
          paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.mx.debitcard.id,
          card: {
            name: Faker.name.findName(),
            number: defaults._globals.cardsWhitelist.mastercard,
            expiryDate: '12/22',
            cvv: '123',
          },
        };

        woocommerce
          .buyWonderWomansPurseWithDebitCardToPersonal(mock(mockData));
      });
    });

    context('Credit Card', () => {
      it('can buy `wonder womans purse`, using credit card and create account without one-click', () => {
        const mockData = {
          paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.mx.creditcard.id,
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

    context('Spei', () => {
      it('can buy `wonder womans purse`, using Spei to personal', () => {
        const mockData = {
          paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.mx.spei.id,
        };

        woocommerce
          .buyWonderWomansPurseWithSpeiToPersonal(mock(mockData));
      });
    });
  });
});
