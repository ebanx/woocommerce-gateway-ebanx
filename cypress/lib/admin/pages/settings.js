const settingsPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        ebanxSettings: '#toplevel_page_admin-page-wc-settings-tab-checkout-section-ebanx-global a',
        saveSettings: '#mainform > p.submit > input.button-primary.woocommerce-save-button'
      },
      fields: {
        sandboxIntegrationKey: '#woocommerce_ebanx-global_sandbox_private_key',
        sandboxPublickKey: '#woocommerce_ebanx-global_sandbox_public_key'
      },
      containers: {
        successMessage: '#message',
        errorMessage: '#mainform > div.notice.notice-error'
      }
    }
  };

  const { buttons, fields, containers } = _private.elements;

  const $public = {
    open: function () {
      test
        .get(buttons.ebanxSettings, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true });

      return this;
    },

    fillSandboxKeys: function (keys) {
      test
        .get(fields.sandboxIntegrationKey, { timeout: 10000 })
          .should('be.visible')
          .clear()
          .type(keys.integration_key)
        .get(fields.sandboxPublickKey, { timeout: 10000 })
          .should('be.visible')
          .clear()
          .type(keys.public_key);
        
      return this;
    },

    saveSettings: function () {
      test
        .get(buttons.saveSettings, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true })
        .get(containers.successMessage, { timeout: 10000 })
          .should('be.visible');

      return this;
    }
  };

  return $public;
});

module.exports = settingsPage;