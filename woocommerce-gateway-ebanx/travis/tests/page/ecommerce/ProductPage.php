<?php

namespace Ebanx\Woocommerce\Page\Ecommerce;

use Ebanx\Woocommerce\Page\BasePage;

class ProductPage extends BasePage {
    public function addToCart() {
        $this->baseTest->byClassName('single_add_to_cart_button')->click();

        return $this;
    }
    public function openCart() {
        $this->baseTest->byLinkText('View Cart')->click();

        return $this->cartPage();
    }
    public function cartPage() {
        return new CartPage($this->baseTest);
    }
}