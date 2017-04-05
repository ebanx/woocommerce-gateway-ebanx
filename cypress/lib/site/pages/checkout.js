const utils = require('../../utils');
const checkoutSchema = require('../schemas/checkout');

const checkoutPage = (function (test) {
  const _private = {
    elements: {
      buttons: {
        placeOrder: '#place_order',
        boletoRadio: '.payment_method_ebanx-banking-ticket label',
        ccRadio: '.payment_method_ebanx-credit-card-br > label',
        tefRadio: '.payment_method_ebanx-tef > label',
        itauRadio: '#ebanx-tef-payment .ebanx-label input[value="itau"]',
        bradescoRadio: '#ebanx-tef-payment .ebanx-label input[value="bradesco"]',
        bbRadio: '#ebanx-tef-payment .ebanx-label input[value="bancodobrasil"]',
        banrisulRadio: '#ebanx-tef-payment .ebanx-label input[value="banrisul"]',
        ebanxAccountRadio: '.payment_method_ebanx-account > label'
      },
      containers: {
        boletoBox: '.payment_box.payment_method_ebanx-banking-ticket',
        tefBox: '.payment_box.payment_method_ebanx-tef',
        accountBox: '.payment_box.payment_method_ebanx-account',
        boletoBarCode: '.banking-ticket__barcode-code',
        checkoutForm: 'form.checkout',
        paymentType: '.ebanx-payment-type'
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
        country: '#billing_country',
        state: '#billing_state',
        hash: 'input[name="ebanx_payment_hash"]',
        ccName: '#ebanx-card-holder-name',
        ccNumber: '#ebanx-card-number',
        ccDueDate: '#ebanx-card-expiry',
        ccCVV: '#ebanx-card-cvv',
        ccInstalments: '#ebanx-container-new-credit-card .ebanx-instalments',
      }
    }
  };

  const { buttons, fields, containers } = _private.elements;

  _private.fillField = (value, field) => {
    test
      .get(field)
        .should('be.visible')
        // .clear()
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

  _private.fillSelect = function (field, value) {
    test
      .get(field, { timeout: 10000 })
        .select(value, { force: true })
        .then($select => {
          $select.change();
        })
        .should('contain', value);
  };

  _private.fillCountry = function (country) {
    _private.fillSelect(fields.country, country);
  };

  _private.fillState = function (state) {
    _private.fillSelect(fields.state, state);
  };

  _private.fillInstalments = function (instalments = '1') {
    _private.fillSelect(fields.ccInstalments, instalments);
  };

  const $public = {
    fillCheckout: function (data) {
      utils
        .validate(data, checkoutSchema, () => {
          _private.fillCountry(data.country);
          _private.fillState(data.state);
          _private.fillPostcode(data.postcode);
          _private.fillCity(data.city);
          _private.fillAddress(data.address);
          _private.fillFirstName(data.firstName);
          _private.fillLastName(data.lastName);
          _private.fillCompany(data.company);
          _private.fillEmail(data.email);
          _private.fillPhone(data.phone);
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
        .get(buttons.boletoRadio, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true })
        .get(containers.boletoBox)
          .should('be.visible');

      return this;
    },

    fillCreditCardBrazilGateway: function (cc_data) {
      test
        .wait(1000)
        .get(buttons.ccRadio, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true })
          .then(() => {
            _private.fillField(cc_data.number, fields.ccNumber);
            _private.fillField(cc_data.due_date, fields.ccDueDate);
            _private.fillField(cc_data.cvv, fields.ccCVV);
            
            if (cc_data.instalments) {
              _private.fillInstalments(cc_data.instalments);
            }
          });

      return this;
    },

    fillTef: function () {
      test
        .get(buttons.tefRadio, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true })
        .get(containers.tefBox)
          .should('be.visible');

      return this;
    },

    fillItauGateway: function () {
      this.fillTef();

      test
        .get(buttons.itauRadio, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true });

      return this;
    },

    fillBradescoGateway: function () {
      this.fillTef();

      test
        .get(buttons.bradescoRadio, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true });

      return this;
    },

    fillBBGateway: function () {
      this.fillTef();

      test
        .get(buttons.bbRadio, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true });

      return this;
    },

    fillBanrisulGateway: function () {
      this.fillTef();

      test
        .get(buttons.banrisulRadio, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true });

      return this;
    },

    fillEbanxAccountGateway: function () {
      test
        .get(buttons.ebanxAccountRadio, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true })
        .get(containers.accountBox)
          .should('be.visible')
        .get(buttons.ebanxAccountRadio, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true });

      return this;
    },

    placeOrder: function () {
      test
        .get(buttons.placeOrder, { timeout: 10000 })
          .should('be.visible')
          .click({ force: true })

      return this;
    },

    extractHash: function (cb) {
      return test
        .get(fields.hash, { timeout: 10000 })
          .should('be.hidden')
          .and('have.attr', 'value')
          .then(cb);
    },

    placeOrderBoleto: function (cb) {
      this.placeOrder();

      test
        .get(containers.boletoBarCode, { timeout: 10000 })
          .should('be.visible')
          .and('not.to.be.empty');

      return this.extractHash(cb);   
    },

    placeOrderCreditCardBrazil: function (data, cb) {
      this.placeOrder();

      test
        .get(containers.paymentType, { timeout: 10000 })
          .should('be.visible')
          .and('not.to.be.empty');

      if (data.instalments) {
        test
          .get(containers.paymentType, { timeout: 10000 })
            .should('contain', data.instalments);
      }

      return this.extractHash(cb);   
    },
  };

  return $public;
});

module.exports = checkoutPage;