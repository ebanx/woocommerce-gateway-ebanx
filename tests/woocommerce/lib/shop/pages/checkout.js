import R from 'ramda';
import { pay } from '../../../../defaults';
import { CHECKOUT_SCHEMA } from '../../schemas/checkout';
import { waitUrlHas, validateSchema } from '../../../../utils';

const fillCity = Symbol('fillCity');
const fillInput = Symbol('fillInput');
const fillState = Symbol('fillState');
const fillPhone = Symbol('fillPhone');
const fillEmail = Symbol('fillEmail');
const placeOrder = Symbol('placeOrder');
const waitOverlay = Symbol('waitOverlay');
const fillBilling = Symbol('fillBilling');
const fillAddress = Symbol('fillAddress');
const fillPassword = Symbol('fillPassword');
const fillPostcode = Symbol('fillPostcode');
const fillDocument = Symbol('fillDocument');
const fillLastName = Symbol('fillLastName');
const selectCountry = Symbol('selectCountry');
const fillFirstName = Symbol('fillFirstName');
const selectInCombobox = Symbol('selectInCombobox');
const choosePaymentType = Symbol('choosePaymentType');
const choosePsePayType = Symbol('choosePsePayType');
const fillDebitCardCvv = Symbol('fillDebitCardCvv');
const fillCreditCardCvv = Symbol('fillCreditCardCvv');
const fillDebitCardName = Symbol('fillDebitCardName');
const checkCreateAccount = Symbol('checkCreateAccount');
const fillCreditCardName = Symbol('fillCreditCardName');
const choosePaymentMethod = Symbol('choosePaymentMethod');
const fillDebitCardNumber = Symbol('fillDebitCardNumber');
const fillCreditCardNumber = Symbol('fillCreditCardNumber');
const fillCreditCardExpiryDate = Symbol('fillCreditCardExpiryDate');
const fillDebitCardExpiryDate = Symbol('fillDebitCardExpiryDate');
const simulatorUrl = `${pay.api.url}/simulator/confirm`;

export default class Checkout {
  constructor(cy) {
    this.cy = cy;
  }

  [fillInput] (data, property, input) {
    R.ifElse(
      R.propSatisfies((x) => (x !== undefined), property), (data) => {
        this.cy
          .get(input, { timeout: 30000 })
          .should('be.visible')
          .clear()
          .type(data[property])
          .should('have.value', data[property]);
      },
      R.always(null)
    )(data);
  }

  [fillFirstName] (data) {
    this[fillInput](data, 'firstName', '#billing_first_name');
  }

  [fillLastName] (data) {
    this[fillInput](data, 'lastName', '#billing_last_name');
  }

  [fillAddress] (data) {
    this[fillInput](data, 'address', '#billing_address_1');
  }

  [fillCity] (data) {
    this[fillInput](data, 'city', '#billing_city');
  }

  [choosePsePayType] (data) {
    return R.ifElse(
      R.propSatisfies((x) => (x instanceof Object), 'paymentType'), (data) => {
        const elm = {
          eft: 'select[name="eft"]',
          results: '.select2-results__option',
        };

        this.cy
          .get(elm.eft)
          .should('be.visible')
          .window().then((win) => {
            win.jQuery(elm.eft).select2('open');
          })
          .contains(elm.results, data.paymentType.name)
          .trigger('mouseup')
          .get(elm.eft)
          .should('have.value', data.paymentType.id);
      },
      R.always(null)
    )(data);
  }

  [selectInCombobox] (elm, value, valueId, elmResults = '.select2-results__option') {
    this.cy
      .get(elm)
      .should('be.visible')
      .window().then((win) => {
        win.jQuery(elm).select2('open');
      })
      .contains(elmResults, value)
      .trigger('mouseup')
      .get(elm)
      .should('have.value', valueId);
  }

  [fillState] (data) {
    const elm = {
      field: '#billing_state',
      property: 'state',
      propertyId: 'stateId',
    };

    R.ifElse(
      R.propSatisfies((x) => (x !== undefined), elm.propertyId), (data) => {
        this[selectInCombobox](elm.field, data[elm.property], data[elm.propertyId]);
      },
      R.always(null)
    )(data);

    R.ifElse(
      R.propSatisfies((x) => (x === undefined), elm.propertyId), (data) => {
        this[fillInput](data, elm.property, elm.field);
      },
      R.always(null)
    )(data);
  }

  [fillPostcode] (data) {
    this[fillInput](data, 'zipcode', '#billing_postcode');
  }

  [fillPhone] (data) {
    this[fillInput](data, 'phone', '#billing_phone');
    this[waitOverlay]();
  }

  [fillEmail] (data) {
    this[fillInput](data, 'email', '#billing_email');
    this[waitOverlay]();
  }

  [checkCreateAccount] () {
    this.cy
      .get('#createaccount')
      .should('be.visible')
      .check()
      .should('be.checked');
  }

  [fillPassword] (data) {
    const doc = {
      elm: '#account_password',
      property: 'password',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), doc.property), (data) => {
        this[checkCreateAccount]();
        this[fillInput](data, doc.property, doc.elm);
      },
      R.always(null)
    )(data);
  }

  [waitOverlay] () {
    this.cy
      .get('.blockUI.blockOverlay', { timeout: 30000 })
      .should('be.not.visible');
  }

  [fillDocument] (data) {
    const doc = {
      elm: `#ebanx_billing_${data.country.toLowerCase()}_document`,
      property: 'document',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), doc.property), (data) => {
        this[fillInput](data, doc.property, doc.elm);

        this[waitOverlay]();
      },
      R.always(null)
    )(data);
  }

  [fillDebitCardNumber] (data) {
    const cN = {
      property: 'number',
      elm: '#ebanx-debit-card-number',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), cN.property), (data) => {
        this.cy
          .get(cN.elm)
          .should('be.visible')
          .type(data[cN.property]);
      },
      R.always(null)
    )(data);
  }

  [fillDebitCardName] (data) {
    const cN = {
      property: 'name',
      elm: '#ebanx-debit-card-holder-name',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), cN.property), (data) => {
        this.cy
          .get(cN.elm)
          .should('be.visible')
          .type(data[cN.property]);
      },
      R.always(null)
    )(data);
  }

  [fillDebitCardExpiryDate] (data) {
    const cE = {
      property: 'expiryDate',
      elm: '#ebanx-debit-card-expiry',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), cE.property), (data) => {
        this.cy
          .get(cE.elm)
          .should('be.visible')
          .type(data[cE.property]);
      },
      R.always(null)
    )(data);
  }

  [fillDebitCardCvv] (data) {
    const cE = {
      property: 'cvv',
      elm: '#ebanx-debit-card-cvv',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), cE.property), (data) => {
        this.cy
          .get(cE.elm)
          .should('be.visible')
          .type(data[cE.property]);
      },
      R.always(null)
    )(data);
  }

  [fillCreditCardNumber] (data) {
    const cN = {
      property: 'number',
      elm: '#ebanx-card-number',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), cN.property), (data) => {
        this.cy
          .get(cN.elm)
          .should('be.visible')
          .type(data[cN.property]);
      },
      R.always(null)
    )(data);
  }

  [fillCreditCardName] (data) {
    const cN = {
      property: 'name',
      elm: '#ebanx-card-holder-name',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), cN.property), (data) => {
        this.cy
          .get(cN.elm)
          .should('be.visible')
          .type(data[cN.property]);
      },
      R.always(null)
    )(data);
  }

  [fillCreditCardExpiryDate] (data) {
    const cE = {
      property: 'expiryDate',
      elm: '#ebanx-card-expiry',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), cE.property), (data) => {
        this.cy
          .get(cE.elm)
          .should('be.visible')
          .type(data[cE.property]);
      },
      R.always(null)
    )(data);
  }

  [fillCreditCardCvv] (data) {
    const cE = {
      property: 'cvv',
      elm: '#ebanx-card-cvv',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), cE.property), (data) => {
        this.cy
          .get(cE.elm)
          .should('be.visible')
          .type(data[cE.property]);
      },
      R.always(null)
    )(data);
  }

  [selectCountry] (data) {
    const cT = {
      elm: '#billing_country',
      property: 'country',
      propertyId: 'countryId',
    };

    return R.ifElse(
      R.propSatisfies((x) => (x !== undefined), cT.property), (data) => {
        this[selectInCombobox](cT.elm, data[cT.property], data[cT.propertyId]);
      },
      R.always(null)
    )(data);
  }

  [choosePaymentMethod] (data) {
    const resolveMethod = (method, country) => {
      const elmMethods = {
        ar: {
          creditcard: 'credit-card-ar',
        },
        co: {
          creditcard: 'credit-card-co',
        },
        mx: {
          creditcard: 'credit-card-mx',
          debitcard: 'debit-card',
        },
        br: {
          boleto: 'banking-ticket',
          creditcard: 'credit-card-br',
        },
      };

      return elmMethods[country] && elmMethods[country][method] ? elmMethods[country][method] : method;
    };

    const pM = {
      elm: (method, countryId) => `#payment_method_ebanx-${resolveMethod(method, countryId)}`,
      property: 'paymentMethod',
    };

    R.ifElse(
      R.propSatisfies((x) => (x !== undefined), pM.property), (data) => {
        this.cy
          .get(pM.elm(data[pM.property], data.countryId.toLowerCase()))
          .should('be.visible')
          .click({ force: true });
      },
      R.always(null)
    )(data);
  }

  [choosePaymentType] (elementId, type) {
    this.cy
      .contains(`${elementId} .ebanx-label`, type)
      .find('input[type="radio"]')
      .should('be.visible')
      .click({ force: true });
  }

  [placeOrder] () {
    this.cy
      .get('#place_order')
      .should('be.visible')
      .click({ force: true });
  }

  [fillBilling] (data) {
    this[selectCountry](data);
    this[fillFirstName](data);
    this[fillLastName](data);
    this[fillEmail](data);
    this[fillAddress](data);
    this[fillCity](data);
    this[choosePaymentMethod](data);
    this[fillState](data);
    this[fillPostcode](data);
    this[fillPhone](data);
    this[fillDocument](data);
    this[fillPassword](data);
  }

  placeWithSpei(data, next) {
    validateSchema(CHECKOUT_SCHEMA.mx.spei(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      next();
    });
  }

  placeWithOxxo(data, next) {
    validateSchema(CHECKOUT_SCHEMA.mx.oxxo(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      next();
    });
  }

  placeWithBaloto(data, next) {
    validateSchema(CHECKOUT_SCHEMA.co.baloto(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      next();
    });
  }

  placeWithSafetyPay(data) {
    validateSchema(CHECKOUT_SCHEMA[data.countryId.toLowerCase()].safetyPay(), data, () => {
      this[fillBilling](data);
      this[choosePaymentType]('#ebanx-safetypay-payment', data.paymentType);
      this[placeOrder]();

      // TODO: Move to another place (something like: `pay/pages/simulator`)

      waitUrlHas(simulatorUrl);
    });
  }

  placeWithPse(data) {
    validateSchema(CHECKOUT_SCHEMA.co.pse(), data, () => {
      this[fillBilling](data);
      this[choosePsePayType](data.paymentType);
      this[placeOrder]();

      // TODO: Move to another place (something like: `pay/pages/simulator`)

      waitUrlHas(simulatorUrl);
    });
  }

  placeWithPagoEfectivo(data, next) {
    validateSchema(CHECKOUT_SCHEMA.pe.pagoEfectivo(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      next();
    });
  }

  placeWithBoleto(data, next) {
    validateSchema(CHECKOUT_SCHEMA.br.boleto(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      next();
    });
  }

  placeWithBankTransfer(data, next) {
    validateSchema(CHECKOUT_SCHEMA.br.banktransfer(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      next();
    });
  }

  placeWithDebitCard(data, next) {
    validateSchema(CHECKOUT_SCHEMA[data.countryId.toLowerCase()].debitcard(), data, () => {
      this[fillBilling](data);
      this[fillDebitCardName](data.card);
      this[fillDebitCardNumber](data.card);
      this[fillDebitCardExpiryDate](data.card);
      this[fillDebitCardCvv](data.card);
      this[placeOrder]();

      next();
    });
  }

  placeWithCreditCard(data, next) {
    validateSchema(CHECKOUT_SCHEMA[data.countryId.toLowerCase()].creditcard(), data, () => {
      const instalmentsBR = {
        elm: 'select[name="ebanx-credit-card-installments"]',
        content: data.instalments + 'x',
        value: data.instalments
      };
      this[fillBilling](data);
      this[fillCreditCardName](data.card);
      this[fillCreditCardNumber](data.card);
      this[fillCreditCardExpiryDate](data.card);
      this[fillCreditCardCvv](data.card);
      if (data.instalments) this[selectInCombobox](instalmentsBR.elm, instalmentsBR.content, instalmentsBR.value);
      this[placeOrder]();

      next();
    });
  }

  placeWithTef(data) {
    validateSchema(CHECKOUT_SCHEMA.br.tef(), data, () => {
      this[fillBilling](data);
      this[choosePaymentType]('#ebanx-tef-payment', data.paymentType);
      this[placeOrder]();

      waitUrlHas(`${pay.api.newUrl}/directtefredirect`);
    });
  }

  placeWithSencillito(data) {
    validateSchema(CHECKOUT_SCHEMA.cl.sencillito(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      waitUrlHas(simulatorUrl);
    });
  }

  placeWithServiPag(data) {
    validateSchema(CHECKOUT_SCHEMA.cl.servipag(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      waitUrlHas(simulatorUrl);
    });
  }

  placeWithWebpay(data) {
    validateSchema(CHECKOUT_SCHEMA.cl.webpay(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      waitUrlHas(simulatorUrl);
    });
  }

  placeWithMulticaja(data) {
    validateSchema(CHECKOUT_SCHEMA.cl.multicaja(), data, () => {
      this[fillBilling](data);
      this[placeOrder]();

      waitUrlHas(simulatorUrl);
    });
  }

  placeWithEfectivo(data, next) {
    validateSchema(CHECKOUT_SCHEMA.ar.efectivo(), data, () => {
      this[fillBilling](data);
      this[selectInCombobox]('#ebanx_billing_argentina_document_type', data.documentType, data.documentTypeId);
      this[choosePaymentType]('#ebanx-efectivo-payment', data.paymentType);
      this[placeOrder]();

      next();
    });
  }

  placeWithDocumentError(data) {
    validateSchema(CHECKOUT_SCHEMA.ar.efectivo(), data, () => {
      this[fillBilling](data);
      this[selectInCombobox]('#ebanx_billing_argentina_document_type', data.documentType, data.documentTypeId);
      this[choosePaymentType]('#ebanx-efectivo-payment', data.paymentType);
      this[placeOrder]();

      this.cy
        .get('.woocommerce-error > li:nth-child(1)', { timeout: 30000 })
        .should('be.visible')
        .contains(' must have 11 digits and contain only numbers.');
    });

  }
}
