const productPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        addToCart: '.single_add_to_cart_button',
        viewCart: '.woocommerce-message > .button',
        proceedToCheckout: '.checkout-button'
      }
    }
  };

  const { buttons } = _private.elements;

  const $public = {
    addOpenedToCart: function () {
      test
        .get(buttons.addToCart)
          .should('be.visible')
          .click()
        .get(buttons.viewCart)
          .should('be.visible');

      return this;
    },

    viewCart: function () {
      test
        .get(buttons.viewCart)
          .should('be.visible')
          .click()
        .get(buttons.proceedToCheckout)
          .should('be.visible');

      return this;
    }
  };

  return $public;
});

module.exports = productPage;