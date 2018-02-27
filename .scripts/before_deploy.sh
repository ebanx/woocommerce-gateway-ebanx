#!/usr/bin/env bash

bash <(curl -s https://codecov.io/bash)

rm -rf vendor
composer install --no-dev

zip -r $TRAVIS_BUILD_DIR/ebanx-payment-gateway-for-woocommerce $TRAVIS_BUILD_DIR/. -x "*.git*"
