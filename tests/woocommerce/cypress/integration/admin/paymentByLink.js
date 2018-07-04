/* global Cypress, it, describe, before, context, cy */

import Faker from 'faker';

import Admin from '../../../lib/admin/operator';
import Pay from "../../../../pay/lib/operator";
import { assertUrlStatus, wrapOrderAssertations } from '../../../../utils';

Faker.locale = 'pt_BR';

let admin;
let pay;

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    admin = new Admin(cy);
    pay = new Pay(cy);

    admin.login();
  });

  context('Admin', () => {
    context('PaymentByLink', () => {
      context('Request', () => {
        it('can request payment by link to brazil', () => {
          admin.buyJeans('Brazil', (hash) => {
            pay.queryPayment(hash, Cypress.env('DEMO_INTEGRATION_KEY'), (payment) => {
              const checkoutPayment = Pay.paymentData({
                amount_ext: (Cypress.env('JEANS_PRICE')).toFixed(2),
                payment_type_code: '_all',
                instalments: '1',
                status: 'OP',
              });

              wrapOrderAssertations(payment, checkoutPayment);
            });
          });
        });
      });
    });
  });
});
