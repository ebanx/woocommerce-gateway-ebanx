const siteOperator = (function (test) {
  const _private = {
    pages: {
      home: require('./pages/home')(test),
      product: require('./pages/product')(test),
      cart: require('./pages/cart')(test),
      checkout: require('./pages/checkout')(test)
    }
  };

  const { home, product, cart, checkout } = _private.pages;

  const $public = {
    makePayment: function () {
      home
        .open()
        .openProduct();

      product
        .addOpenedToCart()
        .viewCart();

      cart
        .proceedToCheckout();

      return this;
    },

    makePaymentToBrazil: function (data) {
      this.makePayment();

      checkout.fillToBrazil(data);

      return this;
    },

    makePaymentBoleto: function (data, cb) {
      this.makePaymentToBrazil(data);

      checkout
        .fillBoletoGateway()
        .placeOrderBoleto(cb);

      return this;
    },

    makePaymentCreditCardToBrazil: function (data, cc_data, cb) {
      this.makePaymentToBrazil(data);

      checkout
        .fillCreditCardBrazilGateway(cc_data)
        .placeOrderCreditCardBrazil(cc_data, cb);
    }
  };

  return $public;
});

module.exports = siteOperator;