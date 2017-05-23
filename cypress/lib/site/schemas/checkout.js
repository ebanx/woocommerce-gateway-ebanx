const Joi = require('joi');

module.exports = Joi.object().keys({
  schema: 'Checkout',
  firstName: Joi.string().min(3).max(60).required(),
  lastName: Joi.string().min(3).max(60).required(),
  company: Joi.string(),
  email: Joi.string().email().required(),
  phone: Joi.string().required(),
  country: Joi.string().required(),
  address: Joi.string().required(),
  state: Joi.string().required(),
  postcode: Joi.string().required(),
  city: Joi.string().required()
}).without('schema', ['firstName', 'lastName', 'email', 'phone', 'country', 'address', 'state', 'postcode', 'city']);