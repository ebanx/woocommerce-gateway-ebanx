<?php

namespace Ebanx\Woocommerce\Page\Ecommerce\ResultSearch;

use Ebanx\Woocommerce\Page\Ecommerce\ResultSearchPage;
use Ebanx\Woocommerce\Page\Ecommerce\ProductPage as PrdPage;

class ProductPage extends ResultSearchPage {
    public function assertProductFound($product) {
        $this->baseTest->byLinkText($product);

        return $this;
    }
    public function openProduct($product) {
        $this->baseTest->byLinkText($product)->click();

        return $this->productPage();
    }
    public function productPage() {
        return new PrdPage($this->baseTest);
    }
}