const Joi = require('joi');

module.exports = {
  validate: (schema, data, cb) => {
    Joi.validate(data, schema, (err) => {
      if (err) {
        throw new Error(`Invalid data to schema ${schema._inner.children[0].schema._valids._set[0]}. DETAILS: ${JSON.stringify(err.details)}`);
      }

      return cb();
    });
  }
};