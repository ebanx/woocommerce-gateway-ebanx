#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

setup_test() {
  echo setup_test
  cd $TRAVIS_BUILD_DIR/tests
  npm ci
}

run_tests() {
  echo run_tests
  setup_test
  cd $TRAVIS_BUILD_DIR/tests
  npx cypress run --config video=false --project ./woocommerce -s woocommerce/cypress/integration/shop/$TEST_COUNTRY.js
}

setup_docker() {
  echo setup_docker
  sudo service mysql stop
  cd $TRAVIS_BUILD_DIR
  docker-compose up -d
}

# setup_docker
#
# while ! curl -s http://localhost > /dev/null; do echo waiting for woocommerce-container; sleep 10; done; run_tests
