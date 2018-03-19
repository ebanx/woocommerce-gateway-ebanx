/* global Cypress, it, describe, before, context, cy, expect */

import Faker from 'faker';

import { assertUrlStatus } from '../../../../utils';

Faker.locale = 'pt_BR';


describe('Woocommerce', () => {
  before(() => {
    assertUrlStatus(Cypress.env('DEMO_URL'));
  });

  context('Admin', () => {
    context('PaymentByLink', () => {
      context('Request', () => {
        it('can request payment by link to brazil', () => {
            cy
              .visit(`${Cypress.env('DEMO_URL')}/wp-admin`)
              .get('#user_login', { timeout: 30000 })
              .should('be.visible')
              .type(Cypress.env('ADMIN_USER'))
              .get('#user_pass')
              .should('be.visible')
              .type(Cypress.env('ADMIN_PASSWORD'))
              .get('#wp-submit')
              .should('be.visible')
              .click();

            cy
              .visit(`${Cypress.env('DEMO_URL')}/wp-admin/post-new.php?post_type=shop_order`)
              .get('.button.add-line-item', { timeout: 30000 })
              .should('be.visible')
              .click()
              .get('.button.add-order-item', { timeout: 30000 })
              .should('be.visible')
              .click()
              
              .get('#wc-backbone-modal-dialog > div.wc-backbone-modal > div > section > article > form > span > span.selection > span > ul > li > input')
              .type(Cypress.env('PRODUCT_NAME'))
              .get('.select2-results__option.select2-results__option--highlighted')
              .trigger('mouseup')
              
              .get('#btn-ok')
              .should('be.visible')
              .click()
              
              .get('#select2-customer_user-container')
              .should('be.visible')
              .click()
              .get('body > span > span > span.select2-search.select2-search--dropdown > input')
              .type(Cypress.env('ADMIN_USER'))
              .get('.select2-results__option.select2-results__option--highlighted')
              .trigger('mouseup')

              .get('select#_billing_country')
              .should('be.visible')
              .window().then((win) => {
                win.jQuery('select#_billing_country').select2('open');
              })
              .contains('.select2-results__option', 'Brazil')
              .trigger('mouseup')
              .get('[name="create_ebanx_payment_link"]')
              .should('be.visible')
              .click()
              .get('#order_data > div.order_data_column_container > div:nth-child(1) > div > p:nth-child(4) > input[type="text"]', { timeout: 30000 })
              .then(($elm) => {
                console.log($elm.val());
            });
            // get payment from api validate payment data is ok
        });
      });
    });
  });
});
