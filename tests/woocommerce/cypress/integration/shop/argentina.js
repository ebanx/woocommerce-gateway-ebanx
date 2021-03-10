/* global Cypress, it, describe, before, context, cy */

import R from 'ramda';
import Faker from 'faker';
import defaults from '../../../../defaults';
import {assertUrlStatus, wrapOrderAssertations} from '../../../../utils';
import Woocommerce from '../../../lib/operator';
import Pay from '../../../../pay/lib/operator';

Faker.locale = 'es';

const mock = (data) => (R.merge(
  data,
  {
    firstName: 'MESSI',
    lastName: 'LIONEL ANDRES',
    document: '23-33016244-9',
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
        woocommerce.buyWonderWomansPurseWithPagofacilEfectivoToPersonal(mock(
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

      it('can buy `wonder womans purse` using DNI as document', () => {
          woocommerce.buyWonderWomansPurseWithEfectivoToPersonal(mock(
              {
                  paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.id,
                  paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.types.otrosCupones,
                  document: '1234567',
                  documentType: 'DNI',
                  documentTypeId: 'ARG_DNi',
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
            number: defaults._globals.cardsWhitelist.visa,
            expiryDate: '12/27',
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

    context('Errors', () => {
      it('can`t buy with CUIT document that has less than 11 digits', () => {
        let mockData = mock(
          {
            paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.id,
            paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.types.otrosCupones,
          }
        );
        mockData.document = '23-666';
        woocommerce.cantBuyJeansWithEfectivo(mockData);
      });

      it('can`t buy with DNI document that has less than 7 digits', () => {
          let mockData = mock(
              {
                  paymentMethod: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.id,
                  paymentType: defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.types.otrosCupones,
                  documentType: 'DNI',
                  documentTypeId: 'ARG_DNI',
              }
          );
          mockData.document = '1234';
          woocommerce.cantBuyJeansWithEfectivo(mockData);
      });
    });
  });
});
