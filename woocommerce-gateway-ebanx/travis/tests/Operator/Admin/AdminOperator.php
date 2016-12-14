<?php

namespace Ebanx\Woocommerce\Operator\Admin;

use Ebanx\Woocommerce\Operator\BaseOperator;

use Ebanx\Woocommerce\Page\Admin\LoginPage;
use Ebanx\Woocommerce\Page\Admin\DashboardPage;

class AdminOperator extends BaseOperator {
    public function login(array $userData) {
        $this
            ->loginPage()
            ->open()
            ->fill($userData)
            ->submit()
        ;

        $this->baseTest->waitUntil(function() {
            return $this->dashboardPage()->isLoaded();
        });

        return $this->dashboardPage();
    }

    public function loginPage() {
        return new LoginPage($this->baseTest);
    }

    public function dashboardPage() {
        return new DashboardPage($this->baseTest);
    }
}