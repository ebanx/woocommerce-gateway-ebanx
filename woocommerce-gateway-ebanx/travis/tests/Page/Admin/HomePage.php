<?php

namespace Ebanx\Woocommerce\Page\Admin;

use Ebanx\Woocommerce\Page\BasePage;

class HomePage extends BasePage {
    public function open() {
        $this->baseTest->url('/');

        return $this;
    }
}