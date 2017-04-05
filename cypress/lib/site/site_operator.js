const siteOperator = (function (test) {
  const _private = {
    pages: {
      home: require('./pages/home')(test),
      product: require('./pages/product')(test),
      cart: require('./pages/cart')(test),
      checkout: require('./pages/checkout')(test),
      tef: require('./pages/tef')(test)
    }
  };

  const { home, product, cart, checkout, tef } = _private.pages;

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

    makeBoletoPayment: function (data, cb) {
      this.makePaymentToBrazil(data);

      checkout
        .fillBoletoGateway()
        .placeOrderBoleto(cb);

      return this;
    },

    makeCreditCardToBrazilPayment: function (data, cc_data, cb) {
      this.makePaymentToBrazil(data);

      checkout
        .fillCreditCardBrazilGateway(cc_data)
        .placeOrderCreditCardBrazil(cc_data, cb);
    },

    makeItauPayment: function (data, cb) {
      this.makePaymentToBrazil(data);

      checkout
        .fillItauGateway()
        .placeOrder();

      tef
        .fillYes();
    },

    makeBradescoPayment: function (data, cb) {
      this.makePaymentToBrazil(data);

      checkout
        .fillBradescoGateway()
        .placeOrder();

      tef
        .fillYes();
    },

    makeBBPayment: function (data, cb) {
      this.makePaymentToBrazil(data);

      checkout
        .fillBBGateway()
        .placeOrder();

      tef
        .fillYes();
    },

    makeBanrisulPayment: function (data, cb) {
      this.makePaymentToBrazil(data);

      checkout
        .fillBanrisulGateway()
        .placeOrder();

      tef
        .fillYes();
    },
  };

  return $public;
});

module.exports = siteOperator;