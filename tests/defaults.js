export default {
  _globals: {
    cardsWhitelist: {
      mastercard: '5555555555554444',
    },
  },
  pay: {
    url: 'https://sandbox.ebanxpay.com',
    api: {
      url: 'https://sandbox.ebanxpay.com/ws',
      DEFAULT_VALUES: {
        paymentMethods: {
          ar: {
            efectivo: {
              id : 'efectivo',
              types: {
                rapipago: 'Rapipago',
                pagofacil: 'Pagofacil',
                otrosCupones: 'Otros Cupones',
              },
            },
            creditcard: {
              id: 'creditcard',
            },
          },
          cl: {
            sencillito: {
              id: 'sencillito',
            },
            servipag: {
              id: 'servipag',
            },
            webpay: {
              id: 'webpay',
            },
            multicaja: {
              id: 'multicaja',
            },
          },
          co: {
            baloto: {
              id: 'baloto',
            },
            creditcard: {
              id: 'creditcard',
            },
            pse: {
              id: 'eft',
              types: {
                agrario: {
                  name: 'Banco Agrario',
                  id: 'banco_agrario',
                },
              },
            },
          },
          pe: {
            pagoEfectivo: {
              id: 'pagoefectivo',
            },
            safetyPay: {
              id: 'safetypay',
              types: {
                cash: 'Cash',
                online: 'Online',
              },
            },
          },
          ec: {
            safetyPay: {
              id: 'safetypay',
              types: {
                cash: 'Cash',
                online: 'Online',
              },
            },
          },
          mx: {
            oxxo: {
              id: 'oxxo',
            },
            creditcard: {
              id: 'creditcard',
            },
            debitcard: {
              id: 'debitcard',
            },
            spei: {
              id: 'spei',
            },
          },
          br: {
            boleto: {
              id: 'boleto',
            },
            creditcard: {
              id: 'creditcard',
            },
            tef: {
              id: 'tef',
              types: {
                itau: {
                  id: 'itau',
                  label: 'Itaú',
                },
              },
            },
            ebanxbalance: {
              id: 'ebanxbalance',
            },
          },
        },
      },
    },
  },
};
