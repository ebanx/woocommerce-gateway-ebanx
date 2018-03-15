/* global Cypress, it, describe, before, context, cy */

import R from 'ramda';
import Faker from 'faker';
import defaults from '../../../defaults';
import {assertUrlStatus, wrapOrderAssertations} from '../../../utils';
import Woocommerce from '../../lib/operator';
import Pay from '../../../pay/lib/operator';

Faker.locale = 'es';

const mock = (data) => (R.merge(
  data,
  {
    firstName: 'MESSI',
    lastName: 'LIONEL ANDRES',
    document: '23330162449',
    documentType: 'CUIT',
    documentTypeId: 'ARG_CUIT',
    address: Faker.address.streetName(),
    city: Faker.address.city(),
    state: 'Catamarca',
    stateId: 'K',
    zipcode: Faker.address.zipCode(),
    phone: Faker.phone.phoneNumberFormat(2),
    email: Faker.internet.email(),
    country: 'Argentina',
    countryId: 'AR',
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

  context('Argentina', () => {
    context('Efectivo', () => {
      it('can buy `wonder womans purse` using Rapipago to personal', () => {
        woocommerce.buyWonderWomansPurseWithEfectivoToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.id,
            paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.types.rapipago,
          }
        ));
      });
    
      it('can buy `wonder womans purse` using Pagofacil to personal', () => {
        woocommerce.buyWonderWomansPurseWithEfectivoToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.id,
            paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.types.pagofacil,
          }
        ));
      });
    
      it('can buy `wonder womans purse` using OtrosCupones to personal', () => {
        woocommerce.buyWonderWomansPurseWithEfectivoToPersonal(mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.id,
            paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.types.otrosCupones,
          }
        ));
      });
    });

    context('Credit Card', () => {
      it('can buy `wonder womans purse` using credit card', () => {
        const mockData = {
          paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.creditcard.id,
          instalments: '3',
          card: {
            number: defaults._globals.cardsWhitelist.mastercard,
            expiryDate: '12/27',
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
  });
});
