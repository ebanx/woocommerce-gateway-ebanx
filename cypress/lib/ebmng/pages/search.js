const utils = require('../../utils');
// const checkoutSchema = require('../schemas/checkout');

const searchPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        searchPaymentMenu: 'div.navbar.navbar-static-top.navbar-absolute ul:nth-child(1) > li.dropdown.open > ul > li:nth-child(1) > a',
        paymentMenu: 'div.navbar.navbar-static-top.navbar-absolute ul:nth-child(1) > li.dropdown.open > a',
        searchAction: 'div.actions > .btn',
      },
      containers: {

      },
      fields: {
        hash: '#code'
      }
    }
  };

  const { buttons, fields, containers } = _private.elements;

  _private.fillField = (value, element) => {
    test
      .get(element)
        .should('be.visible')
        .clear()
        .type(value)
        .should('have.value', value);
  };

  _private.fillHash = (hash) => {
    _private.fillField(hash, fields.hash);
  };

  const $public = {
    goToSearch: function () {
      test
        .get(buttons.paymentMenu)
          .should('be.visible')
          .click()
        .get(searchMenu)
          .should('be.visible')
          .click();
    },

    searchPaymentByHash: function (hash) {
      this.goToSearch();

      _private.fillHash(hash);

      test
        .get(buttons.searchAction)
          .should('be.visible')
          .click();
    },

    getPaymentByHash: function (hash) {

    },
  };

  return $public;
});

module.exports = searchPage;