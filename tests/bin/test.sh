#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

setup_test() {
  cd $TRAVIS_BUILD_DIR/tests
  npm install

  ls -la ./node_modules
  ls -la ./node_modules/.bin

  sudo service mysql stop
  "export DISPLAY=:99.0"
  "sh -e /etc/init.d/xvfb start"
}

setup_docker() {
  cd $TRAVIS_BUILD_DIR
  docker-compose up --build
}

setup_test
setup_docker

cd $TRAVIS_BUILD_DIR/tests
node ./node_modules/.bin/cypress run --config videoRecording=false --project ./woocommerce -s cypress/integration/$TEST_COUNTRY.js
