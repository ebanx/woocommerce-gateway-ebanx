<?php

namespace Ebanx\Woocommerce\Page\Admin;

use Ebanx\Woocommerce\Page\BasePage;

class LoginPage extends BasePage {
    public function open() {
        $this->baseTest->url('/wp-login.php');

        return $this;
    }

    public function fill(array $userData) {
        $this->loginField()->value($userData[login]);
        $this->passwordField()->value($userData[password]);

        return $this;
    }

    public function submit() {
        $this->loginButton()->click();

        return $this;
    }

    private function loginField() {
        return $this->baseTest->byCssSelector("#user_login");
    }

    private function passwordField() {
        return $this->baseTest->byCssSelector("#user_pass");
    }

    private function loginButton() {
        return $this->baseTest->byCssSelector("#wp-submit");
    }
}