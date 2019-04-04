import R from 'ramda';
import Cart from './shop/pages/cart';
import OrderList from './shop/pages/orderList';
import Checkout from './shop/pages/checkout';
import ThankYou from './shop/pages/thankYou';
import WonderWomansPurse from './shop/pages/wonderWomansPurse';
import {tryNext} from '../../utils';

const buyWonderWomansPurse = Symbol('buyWonderWomansPurse');

export default class Woocommerce {
  constructor(cy) {
    this.cy = cy;
    this.pages = {
      cart: new Cart(cy),
      checkout: new Checkout(cy),
      thankYou: new ThankYou(cy),
      orderList: new OrderList(cy),
      wonderWomansPurse: new WonderWomansPurse(cy),
    };
  }

  [buyWonderWomansPurse]() {
    this.pages.wonderWomansPurse
      .buy();

    this.pages.cart
      .proceedToCheckoutWithOpened();
  }

  buyWonderWomansPurseWithEfectivoToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout
      .placeWithEfectivo(data, () => {
        this.pages.thankYou
          .stillOnEfectivo();
      });


    return this;
  }

  cantBuyJeansWithEfectivo(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout
      .placeWithDocumentError(data);
  }

  buyWonderWomansPurseWithSpeiToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout.placeWithSpei(data, () => {
      this.pages.thankYou
        .stillOnSpei();
    });
  }

  buyWonderWomansPurseWithOxxoToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout.placeWithOxxo(data, () => {
      this.pages.thankYou
        .stillOnOxxo();
    });
  }

  buyWonderWomansPurseWithBalotoToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout.placeWithBaloto(data, () => {
      this.pages.thankYou
        .stillOnBaloto();
    });
  }

  buyWonderWomansPurseWithServiPagToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout
      .placeWithServiPag(data);

    // pages.thankYou
    //   .stillOnServiPag();

    return this;
  }

  buyWonderWomansPurseWithMulticajaToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout
      .placeWithMulticaja(data);

    // pages.thankYou
    //   .stillOnMulticaja();

    return this;
  }

  buyWonderWomansPurseWithWebpayToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout
      .placeWithWebpay(data);

    // pages.thankYou
    //   .stillOnWebpay();

    return this;
  }

  buyWonderWomansPurseWithSencillitoToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout
      .placeWithSencillito(data);

    // pages.thankYou
    //   .stillOnSencillito();

    return this;
  }

  buyWonderWomansPurseWithTefToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout
      .placeWithTef(data);

    // pages.thankYou
    //   .stillOnTef();

    return this;
  }

  buyWonderWomansPurseWithPseToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout.placeWithPse(data);

    // this.pages.thankYou
    //   .stillOnPse();

    return this;
  }

  buyWonderWomansPurseWithSafetyPayToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout.placeWithSafetyPay(data);

    // this.pages.thankYou
    //   .stillOnSafetyPay();

    return this;
  }

  buyWonderWomansPurseWithPagoEfectivoToPersonal(data) {
    this[buyWonderWomansPurse]();

    this.pages.checkout.placeWithPagoEfectivo(data, () => {
      this.pages.thankYou
        .stillOnPagoEfectivo();
    });
  }

  buyWonderWomansPurseWithBoletoToPersonal(data, next) {
    this[buyWonderWomansPurse]();

    this.pages.checkout.placeWithBoleto(data, () => {
      this.pages.thankYou
        .stillOnBoleto((resp) => {
          tryNext(next, resp);
        });
    });
  }

  buyWonderWomansPurseWithBankTransferToPersonal(data, next) {
    this[buyWonderWomansPurse]();

    this.pages.checkout.placeWithBankTransfer(data, () => {
      this.pages.thankYou
        .stillOnBankTransfer();
    });
  }

  buyWonderWomansPurseWithCreditCardToPersonal(data, next) {
    this[buyWonderWomansPurse]();

    this.pages.checkout
      .placeWithCreditCard(data, () => {
        this.pages.thankYou
          .stillOnCreditCard(data.instalments, (resp) => {
            tryNext(next, resp);
          });
      });
  }

  buyWonderWomansPurseWithDebitCardToPersonal(data, next) {
    this[buyWonderWomansPurse]();

    this.pages.checkout
      .placeWithDebitCard(data, () => {
        this.pages.thankYou
          .stillOnDebitCard();

        R.ifElse(
          R.propSatisfies((x) => (x instanceof Function), 'next'), (data) => {
            data.next();
          },
          R.always(null)
        )({ next });
      });
  }

  buyWonderWomansPurseByOneClick(cvv) {
    this.pages.wonderWomansPurse
      .buyByOneClick(cvv);

    this.pages.thankYou
      .stillOnCreditCard();

    return this;
  }

  cancelPayment(orderNumber) {
    this.pages.orderList.cancelPayment(orderNumber);

    return this;
  }
}
