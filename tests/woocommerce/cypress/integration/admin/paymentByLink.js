/* global Cypress, it, describe, before, context, cy, expect */

import Faker from 'faker';

import Admin from '../../../lib/admin/operator';
import { assertUrlStatus } from '../../../../utils';

Faker.locale = 'pt_BR';

let admin;

describe('Admin', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    admin = new Admin(cy);
    admin.login();
  });

  context('Admin', () => {
    context('PaymentByLink', () => {
      context('Request', () => {
        it('can request payment by link to brazil', () => {
            admin.buyJeans('Brazil');
            // get payment from api validate payment data is ok
        });
      });
    });
  });
});
