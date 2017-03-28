const cartPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        proceedToCheckout: '.checkout-button',
        placeOrder: '#place_order'
      }
    }
  };

  const { buttons } = _private.elements;

  const $public = {
    proceedToCheckout: function () {
      test
        .get(buttons.proceedToCheckout)
          .should('be.visible')
          .click()
        .get(buttons.placeOrder)
          .should('be.visible');

      return this;
    }
  };

  return $public;
});

module.exports = cartPage;