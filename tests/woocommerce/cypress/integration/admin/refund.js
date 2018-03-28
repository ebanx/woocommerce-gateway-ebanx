/* global Cypress, it, describe, before, context, cy, expect */

import Faker from 'faker';

import defaults from '../../../../defaults';
import Woocommerce from '../../../lib/operator';
import Admin from '../../../lib/admin/operator';
import { assertUrlStatus } from '../../../../utils';

Faker.locale = 'pt_BR';

let woocommerce;
let admin;

describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));

    woocommerce = new Woocommerce(cy);
    admin = new Admin(cy);

    admin.login();
  });

  context('Admin', () => {
    context('Refund', () => {
      context('Request', () => {
        it('can request refund to brazil payment', () => {
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
            instalments: '3',
            card: {
              number: defaults._globals.cardsWhitelist.mastercard,
              expiryDate: '12/22',
              cvv: '123',
            },
          };

          woocommerce.buyWonderWomansPurseWithCreditCardToPersonal(checkoutData, (resp) => {
            cy
              .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post.php?post=${resp.orderNumber}&action=edit`)
              .get('.button.refund-items', { timeout: 30000 })
              .should('be.visible')
              .click()
              .get('.refund_order_item_qty')
              .should('be.visible')
              .type('1')
              .get('.button.button-primary.do-api-refund', { timeout: 5000 })
              .should('be.visible')
              .click()
              .get('.blockUI.blockOverlay')
              .should('be.not.visible', { timeout: 30000 })
              .get('.order_notes')
              .should('be.visible')
              .then(($ul) => {
                expect($ul.find('li').length).to.equal(3);
              });
          });
        });
      });
    });
  });
});
