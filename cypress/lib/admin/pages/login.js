const defaults = require('../../defaults');

const loginPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        login: '#wp-submit',
        ebanxSettings: '#toplevel_page_admin-page-wc-settings-tab-checkout-section-ebanx-global a',
        skipSetup: 'body.wc-setup > div > p.wc-setup-actions.step > a:nth-child(2)'
      },
      fields: {
        user_login: '#user_login',
        user_pass: '#user_pass'
      },
      containers: {
        sidebar: '#adminmenuwrap'
      }
    }
  };

  const { buttons, fields, containers } = _private.elements;

  const $public = {
    open: function () {
      test
        .visit(defaults.admin.host)
        .get(fields.user_login, { timeout: 10000 })
          .should('be.visible')
        .get(fields.user_pass, { timeout: 10000 })
          .should('be.visible');

      return this;
    },

    login: function () {
      test
        .get(fields.user_login, { timeout: 10000 })
          .clear()
          .type(defaults.admin.login)
          .should('be.visible')
        .get(fields.user_pass, { timeout: 10000 })
          .clear()
          .type(defaults.admin.password)
          .should('be.visible')
        .get(buttons.login, { timeout: 10000 })
          .click({ force: true })
        .location()
          .its('search')
          .then(function (search) {
            if (search === '?page=wc-setup') {
              test
                .get(buttons.skipSetup)
                .click({ force: true });
            }
          })
        .get(containers.sidebar, { timeout: 10000 })
          .should('be.visible')
        .get(buttons.ebanxSettings, { timeout: 10000 })
          .should('be.visible');

      return this;
    }
  };

  return $public;
});

module.exports = loginPage;