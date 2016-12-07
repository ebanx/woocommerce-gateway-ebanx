<?php

namespace Ebanx\Woocommerce\Page\Ecommerce;

use Ebanx\Woocommerce\Page\BasePage;

class CartPage extends BasePage {
    public function proceedToCheckout() {
        $this->baseTest->byLinkText('Proceed to Checkout')->click();

        return $this->checkoutPage();
    }
    public function checkoutPage() {
        return new CheckoutPage($this->baseTest);
    }
}