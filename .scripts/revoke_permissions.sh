#!/usr/bin/env bash

sudo chown -R travis:travis $TRAVIS_BUILD_DIR/vendor
sudo chmod -R 777 $TRAVIS_BUILD_DIR/vendor
chmod +x $TRAVIS_BUILD_DIR/vendor/bin/phpcs
chmod +x $TRAVIS_BUILD_DIR/vendor/bin/phpunit
sudo chown -R travis:travis $TRAVIS_BUILD_DIR/tests
