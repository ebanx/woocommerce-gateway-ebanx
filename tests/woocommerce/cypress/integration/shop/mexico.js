/* global Cypress, it, describe, before, context, cy */

import R from 'ramda';
import Faker from 'faker';
import defaults from '../../../../defaults';
import {assertUrlStatus, wrapOrderAssertations} from '../../../../utils';
import Woocommerce from '../../../lib/operator';
import Pay from "../../../../pay/lib/operator";

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
let pay;

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    pay = new Pay(cy);
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
          instalments: '3',
          card: {
            name: Faker.name.findName(),
            number: defaults._globals.cardsWhitelist.mastercard,
            expiryDate: '12/22',
            cvv: '123',
          },
        };

        woocommerce
          .buyWonderWomansPurseWithCreditCardToPersonal(mock(mockData), (resp) => {
            pay.queryPayment(resp.hash, Cypress.env('DEMO_INTEGRATION_KEY'), (payment) => {
              const checkoutPayment = Pay.paymentData({
                amount_ext: (Cypress.env('JEANS_PRICE') + Cypress.env('DEMO_INTEREST_RATE')).toFixed(2),
                payment_type_code: 'mastercard',
                instalments: '3',
                status: 'CO',
              });

              wrapOrderAssertations(payment, checkoutPayment);
            });
          });
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
