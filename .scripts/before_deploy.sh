#!/usr/bin/env bash

rm -rf vendor
composer install --no-dev

zip -r $TRAVIS_BUILD_DIR/ebanx-payment-gateway-for-woocommerce $TRAVIS_BUILD_DIR/. -x "*.git*"
