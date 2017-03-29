const utils = require('../../utils');
const defaults = require('../../defaults');
const signinSchema = require('../schemas/signin');

const signinPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        login: '#frmLogin > div.actions > .btn'
      },
      containers: {
        ebanxLogo: 'a.brand',
      },
      fields: {
        login: '#txt_login',
        password: '#txt_senha',
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

  _private.fillUsername = (username) => {
    _private.fillField(username, fields.login);
  };

  _private.fillPassword = (password) => {
    _private.fillField(password, fields.password);
  };

  const $public = {
    open: function () {
      test
        .visit(defaults.ebmng.host)
        .get(fields.login)
          .should('be.visible');

      return this;
    },

    login: function (user) {
      utils.validate(user, signinSchema, () => {
        _private.fillUsername(user.username);
        _private.fillPassword(user.password);

        this.submitLogin();
      });
    },

    submitLogin: function () {
      test
        .get(buttons.login)
          .should('be.visible')
          .click()
        .get(containers.ebanxLogo)
          .should('be.visible');
    }
  };

  return $public;
});

module.exports = signinPage;