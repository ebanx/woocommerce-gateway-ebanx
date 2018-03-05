# E2E TESTS ❤

Our e2e tests, is an assortment of [Cypress](https://www.cypress.io/) artifacts, to facilitate and clarify the creation, execution and maintenance of end-to-end automated tests that run covering several journeys of EBANX Woocommerce Plugin.

## SETUP

### REQUIREMENTS

- node >=7
- yarn >=1.3 or npm >=3

On the project directory, run the installation step using [yarn](https://yarnpkg.com/en/) or [npm](https://www.npmjs.com/):

```ssh
yarn # or npm install
```

## RUN

To run tests, you can do:

```ssh
npm run test:woocommerce
```

You can run without video recording (which is faster):

```ssh
npm run test:no-video
```

You can also use [Cypress App](https://www.cypress.io/how-it-works/) to simplify development, run and debug test. Just run:

```
npm run cypress:open
```

## Collaborators

Our test interface provides some useful objects and frameworks to help you in your test suite.

1. [Faker](https://github.com/marak/Faker.js/)
2. [Utils.js]()
3. [Defaults.js]()
4. [Ramda](http://ramdajs.com/)
5. [Joi](https://github.com/hapijs/joi)

### Operators 

Operators are responsible for performing high-level functions across different journeys.
Even though all operator are initially implemented using the _actual_ user interface, i.e. using the browser, the plan is to provide alternate interchangeable operators, that either simulate or bypass the public API in order to achieve faster tests.


#### Example

*UI* example:

```js
import Cart from './pages/cart';
import MyOrders from './pages/myOrders';
import WonderWomansPurse from './pages/wonderWomansPurse';

const buyWonderWomansPurse = Symbol('buyWonderWomansPurse');

export default class Woocommerce {
  constructor(cy) {
    this.cy = cy;
    this.pages = {
      cart: new Cart(cy),
      myOrders: new MyOrders(cy),
      wonderWomansPurse: new WonderWomansPurse(cy),
    };
  }

  [buyWonderWomansPurse]() {
    this.pages.wonderWomansPurse
      .buy();

    this.pages.cart
      .proceedToCheckoutWithOpened();
  }

  buyWonderWomansPurseByOneClick(cvv) {
    this.pages.wonderWomansPurse
      .buyByOneClick(cvv);

    this.pages.myOrders
      .stillOnView();

    return this;
  }
}
```

*API* example: 

```js
export default class Api {
  constructor(cy) {
    this.cy = cy;
  }

  sampleRequest() {
    cypress
      .request({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: {
          sample: '...'
        },
        url: '...'
      })
      .then((response) => {
        if (response.status != 200) {
          throw new Error(JSON.stringify(response));
        }

        return response;
      });
  }
}
```

### Pages 

Pages are responsible for providing DOM object manipulation.

#### Example

```js
/* global Cypress, expect */

const url = Symbol('url');

export default class WonderWomansPurse {
  constructor(cy) {
    this.cy = cy;
  }

  [url]() {
    return `${Cypress.env('DEMO_URL')}/index.php/wonder-woman-s-purse.html`;
  }

  visit() {
    this.cy
      .visit(this[url]())
      .url()
      .then(($url) => {
        expect($url).to.equal(this[url]());
      });

    return this;
  }
}

```

### Schemas 

Schemas are responsible for providing system common objects validations, using [Joi.js](https://github.com/hapijs/joi).

#### Example

```js
import R from 'ramda';
import Joi from 'joi';
import defaults from '../../../defaults';

export const CHECKOUT_SCHEMA = {
  pe: {
    compliance: () => ({
      city: Joi.string().required(),
      phone: Joi.string().required(),
      email: Joi.string().required(),
      state: Joi.string().required(),
      country: Joi.string().required(),
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
            defaults.pay.api.DEFAULT_VALUES.paymentMethods.pe
          )
        )
      ).optional(),
    }),
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
};
```

Your schema should always contain the `schema` property.

### Elements:

1. Your operator should never provide page access to your tests.
2. Your operators and pages should whenever possible return `self` to use chain pattern call methods.
3. Your page should never provide access for your elements.
4. Try to use functional programming and Ramda.js
5. Default values or common test value must be in defaults.js
5. Commons interface values or functions must be in utils.js
6. See Cypress [best practices](https://docs.cypress.io/guides/references/best-practices.html#content-inner)
