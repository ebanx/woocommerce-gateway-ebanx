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
