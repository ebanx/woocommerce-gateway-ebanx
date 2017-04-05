const tefPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        yes: '#mestre > div > div > div > a:nth-child(1)',
      },
      containers: {
        thankYou: '.woocommerce-thankyou-order-received'
      },
      fields: {
        hash: 'input[name="ebanx_payment_hash"]',
      }
    }
  };

  const { buttons, containers, fields } = _private.elements;

  const $public = {
    fillYes: function (cb) {
      test
        .get(buttons.yes, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true })
        .get(containers.thankYou, { timeout: 10000 })
          .should('be.visible');
    }
  };

  return $public;
});

module.exports = tefPage;