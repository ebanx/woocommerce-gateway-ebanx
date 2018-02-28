#!/usr/bin/env bash

sudo chown -R travis:travis $TRAVIS_BUILD_DIR/vendor
sudo chown -R travis:travis $TRAVIS_BUILD_DIR/tests
