#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

list() {
  ls -la $TRAVIS_BUILD_DIR
  ls -la $TRAVIS_BUILD_DIR/tests
}

setup_test() {
  echo setup_test
  cd $TRAVIS_BUILD_DIR/tests
  npm install
  ./node_modules/.bin/cypress -v
  "export DISPLAY=:99.0"
  "sh -e /etc/init.d/xvfb start"
}

run_tests() {
  echo run_tests
  cd $TRAVIS_BUILD_DIR/tests
  ./node_modules/.bin/cypress run --config videoRecording=false --project ./woocommerce -s woocommerce/cypress/integration/$TEST_COUNTRY.js
}

setup_docker() {
  echo setup_docker
  sudo service mysql stop
  cd $TRAVIS_BUILD_DIR
  docker-compose up -d --build
}

list
setup_docker
list
setup_test

while ! curl -s http://localhost > /dev/null; do echo waiting for woocommerce-container; sleep 10; done; run_tests
