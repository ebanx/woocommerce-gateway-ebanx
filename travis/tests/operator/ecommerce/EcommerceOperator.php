<?php

namespace Ebanx\Woocommerce\Operator\Ecommerce;

use Ebanx\Woocommerce\Operator\BaseOperator;
use Ebanx\Woocommerce\Page\Ecommerce\HomePage;
use Ebanx\Woocommerce\Page\Ecommerce\ResultSearch\ProductPage;

class EcommerceOperator extends BaseOperator {
    public function checkoutProduct($product) {
        $this->homePage()
            ->open()
            ->searchProduct($product)
        ;
        $this->productResultPage()
            ->assertProductFound($product)
            ->openProduct($product)
            ->addToCart()
            ->openCart()
        ;
    }

    public function homePage() {
        return new HomePage($this->baseTest);
    }

    public function productResultPage() {
        return new ProductPage($this->baseTest);
    }
}