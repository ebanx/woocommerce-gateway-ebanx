#!/usr/bin/env bash

rm -rf vendor
mv _vendor vendor
sudo chown -R travis:travis $TRAVIS_BUILD_DIR/vendor
