const Joi = require('joi');

module.exports = Joi.object().keys({
  schema: 'Signin',
  username: Joi.string().min(3).max(60).required(),
  password: Joi.string().min(3).max(60).required(),
}).without('schema', ['username', 'password']);