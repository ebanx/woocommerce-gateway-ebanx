#!/usr/bin/env bash

rm -rf vendor
composer install --no-dev

zip -r ebanx-payment-gateway-for-woocommerce . -x "*.git*"
