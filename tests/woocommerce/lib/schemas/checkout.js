import R from 'ramda';
import Joi from 'joi';
import defaults from '../../../defaults';

export const CHECKOUT_SCHEMA = {
  ar: {
    compliance: () => ({
      city: Joi.string().required(),
      phone: Joi.string().required(),
      email: Joi.string().required(),
      document: Joi.string().required(),
      documentType: Joi.string().required(),
      documentTypeId: Joi.string().required(),
      state: Joi.string().required(),
      country: Joi.string().required(),
      stateId: Joi.string().required(),
      zipcode: Joi.string().required(),
      address: Joi.string().required(),
      password: Joi.string().optional(),
      lastName: Joi.string().required(),
      countryId: Joi.string().required(),
      firstName: Joi.string().required(),
      paymentMethod: Joi.any().allow(
        R.pluck('id')(
          R.values(
            defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar
          )
        )
      ).required(),
    }),
    efectivo() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'ArgentinaEfectivo',
            paymentType: Joi.any().allow(
              defaults.pay.api.DEFAULT_VALUES.paymentMethods.ar.efectivo.types
            ).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['paymentType']]);
    },
    creditcard() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'ArgentinaCreditCard',
            instalments: Joi.string().required(),
            card: Joi.object().keys({
              number: Joi.number().required(),
              cvv: Joi.string().required(),
              expiryDate: Joi.string().required(),
            }).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['card']]);
    },
  },
  cl: {
    compliance: () => ({
      city: Joi.string().required(),
      phone: Joi.string().required(),
      email: Joi.string().required(),
      state: Joi.string().required(),
      country: Joi.string().required(),
      zipcode: Joi.string().required(),
      address: Joi.string().required(),
      document: Joi.string().required(),
      password: Joi.string().optional(),
      lastName: Joi.string().required(),
      countryId: Joi.string().required(),
      firstName: Joi.string().required(),
      paymentMethod: Joi.any().allow(
        R.pluck('id')(
          R.values(
            defaults.pay.api.DEFAULT_VALUES.paymentMethods.cl
          )
        )
      ).required(),
    }),
    sencillito() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'ChileSencillito',
          }
        )
      ).without('schema', R.keys(this.compliance()));
    },
    servipag() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'ChileServiPag',
          }
        )
      ).without('schema', R.keys(this.compliance()));
    },
    webpay() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'ChileWebpay',
          }
        )
      ).without('schema', R.keys(this.compliance()));
    },
    multicaja() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'ChileMulticaja',
          }
        )
      ).without('schema', R.keys(this.compliance()));
    },
  },
  co: {
    compliance: () => ({
      city: Joi.string().required(),
      phone: Joi.string().required(),
      email: Joi.string().required(),
      state: Joi.string().required(),
      country: Joi.string().required(),
      zipcode: Joi.string().required(),
      address: Joi.string().required(),
      document: Joi.string().required(),
      password: Joi.string().optional(),
      lastName: Joi.string().required(),
      countryId: Joi.string().required(),
      firstName: Joi.string().required(),
      paymentMethod: Joi.any().allow(
        R.pluck('id')(
          R.values(
            defaults.pay.api.DEFAULT_VALUES.paymentMethods.co
          )
        )
      ).required(),
    }),
    pse() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'ColombiaPse',
            paymentType: Joi.any().allow(
              defaults.pay.api.DEFAULT_VALUES.paymentMethods.co.pse.types
            ).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['paymentType']]);
    },
    baloto() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'ColombiaBaloto',
          }
        )
      ).without('schema', R.keys(this.compliance()));
    },
    creditcard() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'ColombiaCreditCard',
            instalments: Joi.string().required(),
            card: Joi.object().keys({
              name: Joi.string().required(),
              number: Joi.number().required(),
              cvv: Joi.string().required(),
              expiryDate: Joi.string().required(),
            }).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['card']]);
    },
  },
  ec: {
    compliance: () => ({
      city: Joi.string().required(),
      phone: Joi.string().required(),
      email: Joi.string().required(),
      state: Joi.string().required(),
      country: Joi.string().required(),
      zipcode: Joi.string().required(),
      address: Joi.string().required(),
      password: Joi.string().optional(),
      lastName: Joi.string().required(),
      countryId: Joi.string().required(),
      firstName: Joi.string().required(),
      paymentMethod: Joi.any().allow(
        R.pluck('id')(
          R.values(
            defaults.pay.api.DEFAULT_VALUES.paymentMethods.ec
          )
        )
      ).optional(),
    }),
    safetyPay() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'EcuadorSafetyPay',
            paymentType: Joi.any().allow(
              defaults.pay.api.DEFAULT_VALUES.paymentMethods.ec.safetyPay.types
            ).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['paymentType']]);
    },
  },
  pe: {
    compliance: () => ({
      city: Joi.string().required(),
      phone: Joi.string().required(),
      email: Joi.string().required(),
      state: Joi.string().required(),
      document: Joi.string().required(),
      country: Joi.string().required(),
      stateId: Joi.string().required(),
      zipcode: Joi.string().required(),
      address: Joi.string().required(),
      password: Joi.string().optional(),
      lastName: Joi.string().required(),
      countryId: Joi.string().required(),
      firstName: Joi.string().required(),
      paymentMethod: Joi.any().allow(
        R.pluck('id')(
          R.values(
            defaults.pay.api.DEFAULT_VALUES.paymentMethods.pe
          )
        )
      ).required(),
    }),
    safetyPay() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'PeruSafetyPay',
            paymentType: Joi.any().allow(
              defaults.pay.api.DEFAULT_VALUES.paymentMethods.pe.safetyPay.types
            ).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['paymentType']]);
    },
    pagoEfectivo() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'PeruPagoEfectivo',
          }
        )
      ).without('schema', R.keys(this.compliance()));
    },
  },
  mx: {
    compliance: () => ({
      city: Joi.string().required(),
      phone: Joi.string().required(),
      email: Joi.string().required(),
      state: Joi.string().required(),
      country: Joi.string().required(),
      stateId: Joi.string().required(),
      zipcode: Joi.string().required(),
      address: Joi.string().required(),
      password: Joi.string().optional(),
      lastName: Joi.string().required(),
      countryId: Joi.string().required(),
      firstName: Joi.string().required(),
      paymentMethod: Joi.any().allow(
        R.pluck('id')(
          R.values(
            defaults.pay.api.DEFAULT_VALUES.paymentMethods.mx
          )
        )
      ).required(),
    }),
    debitcard() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'MexicoDebitCard',
            card: Joi.object().keys({
              name: Joi.string().required(),
              number: Joi.number().required(),
              cvv: Joi.string().required(),
              expiryDate: Joi.string().required(),
            }).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['card']]);
    },
    creditcard() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'MexicoCreditCard',
            instalments: Joi.string().required(),
            card: Joi.object().keys({
              name: Joi.string().required(),
              number: Joi.number().required(),
              cvv: Joi.string().required(),
              expiryDate: Joi.string().required(),
            }).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['card']]);
    },
    oxxo() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'MexicoOxxo',
          }
        )
      ).without('schema', R.keys(this.compliance()));
    },
    spei() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'MexicoSpei',
          }
        )
      ).without('schema', R.keys(this.compliance()));
    },
  },
  br: {
    compliance: () => ({
      city: Joi.string().required(),
      phone: Joi.string().required(),
      email: Joi.string().required(),
      state: Joi.string().required(),
      country: Joi.string().required(),
      stateId: Joi.string().required(),
      zipcode: Joi.string().required(),
      address: Joi.string().required(),
      password: Joi.string().optional(),
      document: Joi.string().required(),
      lastName: Joi.string().required(),
      countryId: Joi.string().required(),
      firstName: Joi.string().required(),
      paymentMethod: Joi.any().allow(
        R.pluck('id')(
          R.values(
            defaults.pay.api.DEFAULT_VALUES.paymentMethods.br
          )
        )
      ).required(),
    }),
    creditcard() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'BrazilCreditCard',
            instalments: Joi.string().required(),
            card: Joi.object().keys({
              number: Joi.number().required(),
              cvv: Joi.string().required(),
              expiryDate: Joi.string().required(),
            }).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['card']]);
    },
    tef() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          {
            schema: 'BrazilTef',
            paymentType: Joi.any().allow(
              R.pluck('label')(
                R.values(
                  defaults.pay.api.DEFAULT_VALUES.paymentMethods.br.tef.types
                )
              )
            ).required(),
          }
        )
      ).without('schema', [...R.keys(this.compliance()), ...['paymentType']]);
    },
    boleto() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          { schema: 'BrazilBoleto' }
        )
      ).without('schema', R.keys(this.compliance()));
    },
    banktransfer() {
      return Joi.object().keys(
        Object.assign(
          {},
          this.compliance(),
          { schema: 'BrazilBankTransfer' }
        )
      ).without('schema', R.keys(this.compliance()));
    },
  },
};
