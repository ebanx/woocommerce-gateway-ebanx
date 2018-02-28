#!/usr/bin/env bash

sudo chmod -R 777 $TRAVIS_BUILD_DIR/vendor
chown -R travis:travis $TRAVIS_BUILD_DIR/tests
