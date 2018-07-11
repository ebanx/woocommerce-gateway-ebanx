/* global Cypress */
import Order from './pages/order';
import Login from './pages/login';
import Capture from './pages/capture';
import AddOrder from './pages/addOrder';
import Notification from './pages/notification';
import EbanxSettings from './pages/ebanxSettings';

export default class Admin {
  constructor(cy) {
    this.cy = cy;
    this.pages = {
      order: new Order(cy),
      login: new Login(cy),
      capture: new Capture(cy),
      newOrder: new AddOrder(cy),
      notification: new Notification(cy),
      ebanxSettings: new EbanxSettings(cy),
    };
  }

  login() {
    this.pages.login.login();

    return this;
  }

  logout() {
    this.pages.login.logout();

    return this;
  }

  buyJeans(country, next) {
    this.pages.newOrder.visit();

    this.pages.newOrder.placeWithPaymentByLink(country, next);
  }

  notifyPayment(hash) {
    this.pages.notification.send(hash);

    return this;
  }

  toggleManualReviewOption() {
    this.pages.ebanxSettings
      .visit()
      .toggleManualReviewOption();

    return this;
  }

  checkPaymentStatusOnPlatform(orderNumber, status) {
    this.pages.order.paymentHasStatus(orderNumber, status);

    return this;
  }

  toggleCaptureOption() {
    this.pages.ebanxSettings
      .visit()
      .toggleCaptureOption();

    return this;
  }

  captureCreditCardPayment(orderNumber) {
    this.pages.order.capturePayment(orderNumber);

    return this;
  }

  captureCreditCardPaymentThroughAPI(hash) {
    this.pages.capture.request(hash);

    return this;
  }

  refundPayment(orderNumber) {
    this.pages.order.refundOrder(orderNumber);

    return this;
  }
}
