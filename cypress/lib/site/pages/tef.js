const tefPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        yes: '#mestre > div > div > div > a:nth-child(1)',
      },
      containers: {
        thankYou: '.woocommerce-thankyou-order-received'
      }
    }
  };

  const { buttons, containers } = _private.elements;

  const $public = {
    fillYes: function () {
      test
        .get(buttons.yes, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true })
        .get(containers.thankYou, { timeout: 10000 })
          .should('be.visible');

      return this;
    }
  };

  return $public;
});

module.exports = tefPage;