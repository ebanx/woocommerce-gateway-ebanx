/* global Cypress, it, describe, before, context, cy */

import Faker from 'faker';

import defaults from '../../../../defaults';
import Woocommerce from '../../../lib/operator';
import Admin from '../../../lib/admin/operator';
import { assertUrlStatus } from '../../../../utils';

Faker.locale = 'pt_BR';

let woocommerce;
let admin;

const checkoutData = {
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
  paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.br.creditcard.id,
  instalments: '1',
  card: {
    number: defaults._globals.cardsWhitelist.mastercard,
    expiryDate: '12/22',
    cvv: '123',
  },
};

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    woocommerce = new Woocommerce(cy);
    admin = new Admin(cy);
  });

  context('Admin', () => {
    context('Capture', () => {
      it('can capture payment mannually', () => {
        admin
          .login()
          .toggleCaptureOption()
          .logout();

        woocommerce.buyWonderWomansPurseWithCreditCardToPersonal(checkoutData, (resp) => {
          admin
            .login()
            .captureCreditCardPayment(resp.orderNumber)
            .toggleCaptureOption()
            .logout();
        });
      });

      it('can capture and notify through API', () => {
        admin
          .login()
          .toggleCaptureOption()
          .logout();

        woocommerce.buyWonderWomansPurseWithCreditCardToPersonal(checkoutData, (resp) => {
          admin
            .captureCreditCardPaymentThroughAPI(resp.hash)
            .notifyPayment(resp.hash)
            .login()
            .checkPaymentStatusOnPlatform(resp.orderNumber, 'Processing')
            .toggleCaptureOption()
            .logout();
        });
      });
    });
  });
});
