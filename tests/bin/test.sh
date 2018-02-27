#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

setup_test() {
  echo setup_test
  sudo chown -R travis:travis $TRAVIS_BUILD_DIR/tests
  sudo chown -R travis:travis $TRAVIS_BUILD_DIR/vendor

  "export DISPLAY=:99.0"
  "sh -e /etc/init.d/xvfb start"

  cd $TRAVIS_BUILD_DIR/tests
  npm install
}

run_tests() {
  echo run_tests
  setup_test
  cd $TRAVIS_BUILD_DIR/tests
  ./node_modules/.bin/cypress run --config videoRecording=false --project ./woocommerce -s woocommerce/cypress/integration/$TEST_COUNTRY.js
}

setup_docker() {
  echo setup_docker
  sudo service mysql stop
  cd $TRAVIS_BUILD_DIR
  docker-compose up -d --build
}

setup_docker

while ! curl -s http://localhost > /dev/null; do echo waiting for woocommerce-container; sleep 10; done; run_tests
