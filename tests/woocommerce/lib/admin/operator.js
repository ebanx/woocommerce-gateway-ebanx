import R from 'ramda';
import Cart from './pages/cart';
import Checkout from './pages/checkout';
import ThankYou from './pages/thankYou';
import WonderWomansPurse from './pages/wonderWomansPurse';
import {tryNext} from '../../utils';

export default class Admin {
  constructor(cy) {
    this.cy = cy;
    this.pages = {
      cart: new Cart(cy),
      checkout: new Checkout(cy),
      thankYou: new ThankYou(cy),
      wonderWomansPurse: new WonderWomansPurse(cy),
    };
  }
}
