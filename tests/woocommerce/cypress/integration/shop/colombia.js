/* global Cypress, it, describe, before, context, cy */

import R from 'ramda';
import Faker from 'faker';
import defaults from '../../../../defaults';
import {assertUrlStatus, wrapOrderAssertations} from '../../../../utils';
import Woocommerce from '../../../lib/operator';
import Pay from "../../../../pay/lib/operator";

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
    document: '123456789',
    phone: Faker.phone.phoneNumberFormat(2),
    email: Faker.internet.email(),
    country: 'Colombia',
    countryId: 'CO',
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
          instalments: '3',
          card: {
            name: Faker.name.findName(),
            number: defaults._globals.cardsWhitelist.visa,
            expiryDate: '12/22',
            cvv: '123',
          },
        };

        woocommerce
          .buyWonderWomansPurseWithCreditCardToPersonal(mock(mockData), (resp) => {
            pay.queryPayment(resp.hash, Cypress.env('DEMO_INTEGRATION_KEY'), (payment) => {
              const checkoutPayment = Pay.paymentData({
                amount_ext: (Cypress.env('JEANS_PRICE') + Cypress.env('DEMO_INTEREST_RATE')).toFixed(2),
                payment_type_code: 'visa',
                instalments: '3',
                status: 'CO',
              });

              wrapOrderAssertations(payment, checkoutPayment);
            });
          });
      });
    });
  });
});
