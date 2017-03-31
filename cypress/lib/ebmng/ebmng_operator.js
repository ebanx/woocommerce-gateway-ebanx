const ebmngOperator = (function (test) {
  const _private = {
    pages: {
      signin: require('./pages/signin')(test),
      search: require('./pages/search')(test)
    }
  };

  const { signin, search } = _private.pages;

  const $public = {
    signin: function (user) {
      signin
        .open()
        .login(user);
    },

    checkHasPayment: function (hash) {
      search
        .searchPaymentByHash(hash);
    }
  };

  return $public;
});

module.exports = ebmngOperator;