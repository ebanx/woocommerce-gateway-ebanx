#!/usr/bin/env bash

sudo chmod -R 777 $TRAVIS_BUILD_DIR/vendor
sudo chmod -R 777 $TRAVIS_BUILD_DIR/tests/

chown -R travis:travis $TRAVIS_BUILD_DIR/tests/node_modules
sudo chmod -R 777 $TRAVIS_BUILD_DIR/tests/node_modules
