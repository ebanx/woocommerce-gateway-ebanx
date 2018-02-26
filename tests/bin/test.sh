#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

setup_test() {
  echo setup_test
  cd $TRAVIS_BUILD_DIR/tests
  npm install
  sudo service mysql stop
  "export DISPLAY=:99.0"
  "sh -e /etc/init.d/xvfb start"
}

run_tests() {
  echo run_tests
  cd $TRAVIS_BUILD_DIR/tests
  sudo chmod +x ./node_modules/.bin/cypress
  ./node_modules/.bin/cypress run --config videoRecording=false --project ./woocommerce -s cypress/integration/$TEST_COUNTRY.js
}

setup_docker() {
  echo setup_docker
  cd $TRAVIS_BUILD_DIR
  docker-compose up -d --build
}

setup_test
setup_docker

while ! curl -s http://localhost > /dev/null; do echo waiting for woocommerce-container; sleep 10; done; run_tests
