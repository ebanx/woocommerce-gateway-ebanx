#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

setup_test() {
  echo setup_test
  bash $TRAVIS_BUILD_DIR/.scripts/revoke_permissions.sh

  cd $TRAVIS_BUILD_DIR/tests
  npm install
}

run_tests() {
  echo run_tests
  setup_test
  cd $TRAVIS_BUILD_DIR/tests
  bash $TRAVIS_BUILD_DIR/.scripts/revoke_permissions.sh
  ./node_modules/.bin/cypress run --config videoRecording=false --project ./woocommerce -s woocommerce/cypress/integration/$TEST_COUNTRY.js
}

setup_docker() {
  echo setup_docker
  sudo service mysql stop
  cd $TRAVIS_BUILD_DIR
  docker-compose up -d
}

setup_docker

while ! curl -s http://localhost > /dev/null; do echo waiting for woocommerce-container; sleep 10; done; run_tests
