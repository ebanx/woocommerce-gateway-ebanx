[![Build Status](https://travis-ci.com/ebanx/checkout-woocommerce.svg?token=fnHBQhvUoN1zMVexkAyq&branch=master)](https://travis-ci.com/ebanx/checkout-woocommerce)

# EBANX Payment Gateway for WooCommerce

This plugin enables you to integrate your WooCommerce store with the EBANX payment gateway.

## Requirements
* PHP 5.3+
* Wordpress 3.7+
* WooCommerce 2.1+

## Installation
1. Clone the git repo to your Wordpress /wp-content/plugins folder
```
git clone --recursive https://github.com/ebanx-integration/checkout-woocommerce.git
```
2. Visit your WooCommerce settings menu:
    WooCommerce > Settings > Payment Gateways > EBANX
3. Enable the EBANX payment gateway, and add your integration key.
4. Go to the EBANX Merchant Area, then to **Integration > Merchant Options**.
  1. Change the _Status Change Notification URL_ to:
```
{YOUR_SITE}/index.php/ebanx/notify/
```
  2. Change the _Response URL_ to:
```
{YOUR_SITE}/index.php/ebanx/return/
```
5. That's all!

## Build

```bash
bash bin/travis.sh
```

#### TESTS

```bash
docker exec -i -t woocommerce-gateway-ebanx-wp /bin/bash
cd /var/www/html/wp-content/plugins/woocommerce-gateway-ebanx/travis
DISPLAY=:1 xvfb-run java -jar data/selenium-server-standalone-2.53.0.jar
```

**PS**: In another shell

```bash
docker exec -i -t woocommerce-gateway-ebanx-wp /bin/bash
cd /var/www/html/wp-content/plugins/woocommerce-gateway-ebanx/travis
phpunit tests/
```

DtihxwPtghfn9aZP#W
