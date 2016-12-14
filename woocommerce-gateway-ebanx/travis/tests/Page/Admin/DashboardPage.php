<?php

namespace Ebanx\Woocommerce\Page\Admin;

use Ebanx\Woocommerce\Page\BasePage;

class DashboardPage extends BasePage {
    public function open() {
        $this->baseTest->url('/wp-admin/');

        return $this;
    }

    public function isLoaded() {
        return (boolean) $this->adminMenu()->displayed();
    }

    private function adminMenu() {
        return $this->baseTest->byCssSelector("#adminmenu");
    }
}