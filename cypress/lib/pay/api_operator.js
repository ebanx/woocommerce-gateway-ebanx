const defaults = require('../defaults');

const apiOperator = (function (test) {
  const _private = {
    getPayment: function (hash, cb) {
      test
        .request({
          method: 'GET',
          headers: {
            'Content-Type': 'application/json'
          },
          qs: {
            integration_key: defaults.api.integration_key,
            hash
          },
          url: defaults.api.host,
          timeout: 20000
        })
        .then(res => {
          if (res.status != 200 || res.body.status !== 'SUCCESS') {
            throw new Error(JSON.stringify(res));
          }

          if (cb) {
            cb(res.body);
          }

          return this;
        })
    }
  };
 
  const $public = {
    assertPaymentStatus: function (hash, status) {
      _private.getPayment(hash, res => {
        expect(res.payment.status).eql(status);
      });
    }
  };

  return $public;
});

module.exports = apiOperator;