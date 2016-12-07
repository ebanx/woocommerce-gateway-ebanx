<?php

namespace Ebanx\Woocommerce\Page\Ecommerce;

use Ebanx\Woocommerce\Page\BasePage;

class HomePage extends BasePage {
    public function searchProduct($product) {
        $this->baseTest->byName('s')->value($product);
        $this->baseTest->byClassName('search-form')->submit();

        return $this;
    }
    public function open() {
        $this->baseTest->url('/');

        return $this;
    }
}