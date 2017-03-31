const utils = require('../../utils');
const checkoutSchema = require('../schemas/checkout');

const checkoutPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        placeOrder: '#place_order',
        boletoRadio: '.payment_method_ebanx-banking-ticket label'
      },
      containers: {
        boletoBox: '.payment_box.payment_method_ebanx-banking-ticket',
        boletoBarCode: '.banking-ticket__barcode-code'
      },
      fields: {
        firstName: '#billing_first_name',
        lastName: '#billing_last_name',
        companyName: '#billing_company',
        email: '#billing_email',
        phone: '#billing_phone',
        address: '#billing_address_1',
        city: '#billing_city',
        postcode: '#billing_postcode',
        brazilDocument: '#ebanx_billing_brazil_document',
        brazilBirthdate: '#ebanx_billing_brazil_birth_date',
        country: '#s2id_billing_country > a.select2-choice',
        countryLabel: '.select2-result-label',
        countrySelected: '#s2id_billing_country span.select2-chosen',
        state: '#s2id_billing_state > a.select2-choice',
        stateLabel: '.select2-result-label',
        stateSelected: '#s2id_billing_state span.select2-chosen',
      }
    }
  };

  const { buttons, fields, containers } = _private.elements;

  _private.fillField = (value, field) => {
    test
      .get(field)
        .should('be.visible')
        .clear()
        .type(value)
        .should('have.value', value);
  };

  _private.fillFirstName = function (firstName) {
    this.fillField(firstName, fields.firstName);
  };

  _private.fillLastName = function (lastName) {
    this.fillField(lastName, fields.lastName);
  };

  _private.fillCompany = function (companyName) {
    this.fillField(companyName, fields.companyName);
  };

  _private.fillEmail = function (email) {
    this.fillField(email, fields.email);
  };

  _private.fillPhone = function (phone) {
    this.fillField(phone, fields.phone);
  };

  _private.fillAddress = function (address) {
    this.fillField(address, fields.address);
  };

  _private.fillCity = function (city) {
    this.fillField(city, fields.city);
  };

  _private.fillPostcode = function (postcode) {
    this.fillField(postcode, fields.postcode);
  };

  _private.fillBrazilDocument = function (brazilDocument) {
    this.fillField(brazilDocument, fields.brazilDocument);
  };

  _private.fillBrazilBirthDate = function (birthdate) {
    this.fillField(birthdate, fields.brazilBirthdate);
  };

  _private.fillCountry = function (country) {
    test
      .get(fields.country)
        .should('be.visible')
        .click()
      .get(fields.countryLabel)
        .should('be.visible')
        .contains(country)
        .click()
      .get(fields.countrySelected)
        .should('be.visible')
        .should('have.text', country);
  };

  _private.fillState = function (state) {
    test
      .get(fields.state)
        .should('be.visible')
        .click()
      .get(fields.stateLabel)
        .should('be.visible')
        .contains(state)
        .click()
      .get(fields.stateSelected)
        .should('be.visible')
        .should('have.text', state);
  };

  const $public = {
    fillCheckout: function (data) {
      utils
        .validate(data, checkoutSchema, () => {
          _private.fillFirstName(data.firstName);
          _private.fillLastName(data.lastName);
          _private.fillCompany(data.company);
          _private.fillEmail(data.email);
          _private.fillPhone(data.phone);
          _private.fillCountry(data.country);
          _private.fillAddress(data.address);
          _private.fillCity(data.city);
          _private.fillState(data.state);
          _private.fillPostcode(data.postcode);
        });
    },

    fillToBrazil: function (data) {
      this.fillCheckout(data);

      _private.fillBrazilDocument(data.brazilDocument);
      _private.fillBrazilBirthDate(data.brazilBirthdate);

      return this;
    },

    fillBoletoGateway: function () {
      test
        .get(buttons.boletoRadio, { timeout: 5000 })
          .should('be.visible')
          .click({ force: true })
        .get(containers.boletoBox)
          .should('be.visible');

      return this;
    },

    placeOrder: function () {
      test
        .get(buttons.placeOrder)
          .should('be.visible')
          .click({ force: true });

      return this;
    },

    placeOrderBoleto: function () {
      this.placeOrder();

      test
        .get(containers.boletoBarCode, { timeout: 5000 })
          .should('be.visible')
          .should('not.to.be.empty');

      return this;
    }
  };

  return $public;
});

module.exports = checkoutPage;