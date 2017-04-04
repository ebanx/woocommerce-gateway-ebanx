module.exports = {
  site: {
    host: 'http://localhost:3000/',
    payments: {
      credit_card: {
        visa: '4111 1111 1111 1111',
        master: '5555 5555 5555 4444',
        amex: '378282246310005',
        elo: '6362970000457013',
        hipercard: '6062825624254001',
        carnet: '5062224607440872',
        discover: '6011111111111117'
      }
    }
  },

  admin: {
    host: 'http://localhost:3000/wp-admin/'
  },

  api: {
    integration_key: '1231000',
    host: `https://sandbox.ebanx.com/ws/query`
  }
};