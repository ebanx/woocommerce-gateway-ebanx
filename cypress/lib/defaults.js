module.exports = {
  site: {
    host: Cypress.env('WC_PLUGIN_SITE_HOST') || 'http://localhost/shop/',
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
    host: Cypress.env('WC_PLUGIN_ADMIN_HOST') || 'http://localhost/wp-login.php',
    login: 'ebanx',
    password: 'ebanx'
  },

  api: {
    integration_key: 'b9fa8b1d4231889a1445e1ebfdfa2a3558ef3cc651d523eb1b12cdf8af3afc518f17b36edf5e8cfc59a8338bb9241aacd427',
    public_key: 'yBy5zUmLxop5pUyKTr5CsNGRjnUkmaup8Ha9wwjU8Sg',
    host: `https://sandbox.ebanx.com/ws/query`
  }
};