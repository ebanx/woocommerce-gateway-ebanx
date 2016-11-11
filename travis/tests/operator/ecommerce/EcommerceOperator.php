<?php

namespace Ebanx\Woocommerce\Operator\Ecommerce;

use Ebanx\Woocommerce\Operator\BaseOperator;
use Ebanx\Woocommerce\Page\Ecommerce\HomePage;
use Ebanx\Woocommerce\Page\Ecommerce\ResultSearch\ProductPage;

class EcommerceOperator extends BaseOperator {
    public function checkoutProduct($product) {
        return $this->addProductToCart($product)
            ->proceedToCheckout()
            ->assertSingleProductPresent($product)
        ;
    }

    public function addProductToCart($product) {
        return $this
            ->searchProduct($product)
            ->openProduct($product)
            ->addToCart()
            ->openCart()
        ;
    }

    public function searchProduct($product) {
        $this->homePage()
            ->open()
            ->searchProduct($product)
        ;

        return $this->productResultPage()
            ->assertProductFound($product);
    }

    public function buyProduct($product, array $customerData) {
        return $this
            ->checkoutProduct($product)
            ->fillCustomerData($customerData)
        ;
    }

    public function buyProductPayWithCreditCard($product, array $checkoutData) {
        return $this
            ->buyProduct($product, $checkoutData[customer])
            ->fillCreditCard($checkoutData[payment_data])
            ->placeOrder()
            ->assertCheckoutPaidSuccess()
        ;
    }

    public function homePage() {
        return new HomePage($this->baseTest);
    }

    public function productResultPage() {
        return new ProductPage($this->baseTest);
    }
}