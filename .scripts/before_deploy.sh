#!/usr/bin/env bash

bash <(curl -s https://codecov.io/bash)

mv vendor _vendor
composer install --no-dev

zip -r /tmp/ebanx-payment-gateway-for-woocommerce $TRAVIS_BUILD_DIR/. -x "*.git*" "*tests*" "*_vendor*"
