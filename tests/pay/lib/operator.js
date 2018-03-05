/* global Cypress */

import R from 'ramda';
import defaults from '../../defaults';
import { composeName } from '../../utils';

export default class Pay {
  constructor(cy) {
    this.cy = cy;
  }

  static customerData (checkoutData, object = {}) {
    return R.merge({
      email: R.pipe(R.pick(['email']), R.values, R.join(''), R.toLower)(checkoutData),
      name: composeName(checkoutData),
    }, object);
  }

  static paymentData (data) {
    return R.merge({
      currency_ext: Cypress.env('DEMO_CURRENCY'),
      user_value_5: Cypress.env('DEMO_BENJAMIN'),
      user_value_1: Cypress.env('DEMO_PLATFORM'),
      user_value_3: Cypress.env('DEMO_PLATFORM_VERSION'),
    }, data);
  }

  queryPayment(hash, integrationKey, next) {
    this.cy
      .request({
        method: 'GET',
        url: `${defaults.pay.api.url}/query?hash=${hash}&integration_key=${integrationKey}`,
      })
      .then((response) => {
        if (response.status !== 200 || response.body.status !== 'SUCCESS') {
          throw new Error(JSON.stringify(response));
        }

        R.ifElse(
          R.propSatisfies((x) => (x instanceof Function), 'next'), (data) => {
            data.next(response.body.payment);
          },
          R.always(null)
        )({ next });
      });
  }  
}
