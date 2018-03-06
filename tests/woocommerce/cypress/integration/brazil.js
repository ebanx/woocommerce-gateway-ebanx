/* global Cypress, it, describe, before, context, cy */

import R from 'ramda';
import Faker from 'faker';
import defaults from '../../../defaults';
import { assertUrlStatus } from '../../../utils';
import Woocommerce from '../../lib/operator';

Faker.locale = 'pt_BR';

const mock = (data) => (R.merge(
  data,
  {
    firstName: Faker.name.firstName(),
    lastName: Faker.name.lastName(),
    address: Faker.address.streetName(),
    city: Faker.address.city(),
    state: 'Bahia',
    stateId: 'BA',
    country: 'Brazil',
    countryId: 'BR',
    zipcode: '80230180',
    phone: Faker.phone.phoneNumberFormat(2),
    email: Faker.internet.email(),
    document: '278.517.215-98',
  }
));

let woocommerce;

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    woocommerce = new Woocommerce(cy);
  });

  context('Brazil', () => {
    context('Boleto', () => {
      it('can buy `wonder womans purse` using boleto to personal', () => {
        woocommerce.buyWonderWomansPurseWithBoletoToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.br.boleto.id,
          }
        ));
      });
    });

    context('Credit Card', () => {
      it('can buy `wonder womans purse`, create account and can one-click', () => {
        const mockData = {
          paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.br.creditcard.id,
          instalments: '3',
          card: {
            number: defaults._globals.cardsWhitelist.mastercard,
            expiryDate: '12/22',
            cvv: '123',
          },
          password: Faker.internet.password(),
        };

        woocommerce
          .buyWonderWomansPurseWithCreditCardToPersonal(mock(mockData), () => {
            woocommerce.buyWonderWomansPurseByOneClick(mockData.card.cvv);
          });
      });
    });

    context('Tef', () => {
      it('can buy `wonder womans purse` using tef (Itaú) to personal', () => {
        woocommerce.buyWonderWomansPurseWithTefToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.br.tef.id,
            paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.br.tef.types.itau.label,
          }
        ));
      });
    });
  });
});
