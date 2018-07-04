/* global Cypress, it, describe, before, context, cy, expect */

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
        admin.login();

        cy
          .visit(`${Cypress.env('DEMO_URL')}/wp-admin/admin.php?page=wc-settings&tab=checkout&section=ebanx-global`)
          .get('#woocommerce_ebanx-global_payments_options_title', { timeout: 30000 })
          .should('be.visible')
          .click()
          .get('#woocommerce_ebanx-global_capture_enabled', { timeout: 5000 })
          .should('be.visible')
          .click()
          .get('#mainform > p.submit > button', { timeout: 5000 })
          .should('be.visible')
          .click();

        admin.logout();

        woocommerce.buyWonderWomansPurseWithCreditCardToPersonal(checkoutData, (resp) => {
          admin.login();

          cy
            .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post.php?post=${resp.orderNumber}&action=edit`)
            .get('#select2-order_status-container')
            .should('contain', 'On hold')
            .get('select[name="wc_order_action"]')
            .should('be.visible')
            .select('Capture payment on EBANX')
            .should('have.value', 'ebanx_capture_order')
            .get('.save_order')
            .should('be.visible')
            .click();

          cy.get('div.notice:nth-child(4) > p:nth-child(1)').contains(`Payment ${resp.orderNumber} was captured successfully.`);
        });

        cy
          .visit(`${Cypress.env('DEMO_URL')}/wp-admin/admin.php?page=wc-settings&tab=checkout&section=ebanx-global`)
          .get('#woocommerce_ebanx-global_capture_enabled', { timeout: 5000 })
          .should('be.visible')
          .click()
          .get('#mainform > p.submit > button', { timeout: 5000 })
          .should('be.visible')
          .click();

        admin.logout();
      });

      it('can capture and notify through API', () => {
        admin.login();

        cy
          .visit(`${Cypress.env('DEMO_URL')}/wp-admin/admin.php?page=wc-settings&tab=checkout&section=ebanx-global`)
          .get('#woocommerce_ebanx-global_payments_options_title', { timeout: 30000 })
          .should('be.visible')
          .click()
          .get('#woocommerce_ebanx-global_capture_enabled', { timeout: 5000 })
          .should('be.visible')
          .click()
          .get('#mainform > p.submit > button', { timeout: 5000 })
          .should('be.visible')
          .click();

        admin.logout();

        woocommerce.buyWonderWomansPurseWithCreditCardToPersonal(checkoutData, (resp) => {
          cy.request('GET', `${defaults.pay.api.url}/capture/?integration_key=${Cypress.env('DEMO_INTEGRATION_KEY')}&hash=${resp.hash}`);

          admin.notifyPayment(resp.hash);

          admin.login();
          cy
            .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post.php?post=${resp.orderNumber}&action=edit`)
            .get('#select2-order_status-container')
            .should('contain', 'Processing');
        });

        cy
          .visit(`${Cypress.env('DEMO_URL')}/wp-admin/admin.php?page=wc-settings&tab=checkout&section=ebanx-global`)
          .get('#woocommerce_ebanx-global_capture_enabled', { timeout: 5000 })
          .should('be.visible')
          .click()
          .get('#mainform > p.submit > button', { timeout: 5000 })
          .should('be.visible')
          .click();

        admin.logout();
      });
    });
  });
});
