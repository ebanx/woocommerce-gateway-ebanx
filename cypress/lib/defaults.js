module.exports = {
  site: {
    host: 'http://localhost:3000/'
  },

  admin: {
    host: 'http://localhost:3000/wp-admin/'
  },

  ebmng: {
    host: 'https://sandbox.ebanx.com/ebmng',
    username: process.env.EBANX_WC_EBMNG_SANDBOX_USER,
    password: process.env.EBANX_WC_EBMNG_SANDBOX_PASSWORD
  }
};