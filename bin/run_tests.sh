#!/bin/bash

set -e

echo INSTALL WP CLI

cd /var/www/html/wp-content

curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar

chmod +x wp-cli.phar

mv wp-cli.phar /usr/local/bin/wp

wp --info --allow-root

echo INSTALL WOOCOMMERCE

wp plugin install woocommerce --activate --allow-root

echo ACTIVATE EBANX PLUGIN

wp plugin activate woocommerce-gateway-ebanx --allow-root

cd /var/www/html/wp-content/plugins/woocommerce-gateway-ebanx/travis

echo INSTALL PHPUNIT

wget https://phar.phpunit.de/phpunit-old.phar

chmod +x phpunit-old.phar

mv phpunit-old.phar /usr/local/bin/phpunit

echo INSTALL COMPOSER

rm -rf vendor/ composer.lock

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

composer install

exit
