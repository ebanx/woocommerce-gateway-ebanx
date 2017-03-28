const defaults = require('../../defaults');

const homePage = (function (test) {
  const _private = {
    elements: {
      anchors: {
        product: '.woocommerce-LoopProduct-link'
      },
      buttons: {
        addToCart: '.single_add_to_cart_button'
      }
    }
  };

  const { anchors, buttons } = _private.elements;

  const $public = {
    open: function () {
      test
        .visit(defaults.site.host)
        .get(anchors.product)
          .should('be.visible');

      return this;
    },

    openProduct: function () {
      test
        .get(anchors.product)
          .should('be.visible')
          .click()
        .get(buttons.addToCart)
          .should('be.visible');

      return this;
    }
  };

  return $public;
});

module.exports = homePage;