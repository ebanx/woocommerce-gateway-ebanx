<?php

class BasePage {
    protected $baseTest;
    public function __construct(PHPUnit_Extensions_Selenium2TestCase $baseTest) {
        $this->baseTest = $baseTest;
    }
}

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

class ResultSearchPage extends BasePage {}

class ProductResultPage extends ResultSearchPage {
    public function assertProductFound($product) {
        $this->baseTest->byLinkText($product);

        return $this;
    }
    public function openProduct($product) {
        $this->baseTest->byLinkText($product)->click();

        return $this->productPage();
    }
    public function productPage() {
        return new ProductPage($this->baseTest);
    }
}

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

class CartPage extends BasePage {}

class BaseOperator {
    protected $baseTest;
    public function __construct(PHPUnit_Extensions_Selenium2TestCase $baseTest) {
        $this->baseTest = $baseTest;
    }
}

class EcommerceOperator extends BaseOperator {
    public function checkoutProduct($product) {
        $this->homePage()
            ->open()
            ->searchProduct($product);
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
        return new ProductResultPage($this->baseTest);
    }
}


class BaseTest extends PHPUnit_Extensions_Selenium2TestCase {
    protected function setUp() {
        $this->setHost('localhost');
        $this->setPort(4444);
        $this->setBrowserUrl('http://127.0.0.1/');
        $this->setBrowser('firefox');
    }
    public function tearDown() {
        $this->stop();
    }
}

class CartTest extends  BaseTest {
    protected $mock = array(
        product => 'Acme'
    );

    public function testBuyAcmeProduct() {
        $ecommerceOperator = new EcommerceOperator($this);
        $ecommerceOperator->checkoutProduct($this->mock[product]);
    }
}