#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

run_test() {
  cd $TRAVIS_BUILD_DIR/tests
  node -v
  npm -v
  npm install
  node ./node_modules/.bin/cypress run --config videoRecording=false --project ./woocommerce -s cypress/integration/$TEST_COUNTRY.js
}

setup_docker() {
  cd $TRAVIS_BUILD_DIR
  docker-compose up -d --build
  sleep 20 # REMOVE THAT
}

setup_travis() {
  sudo service mysql stop
  "export DISPLAY=:99.0"
  "sh -e /etc/init.d/xvfb start"
  sleep 3
}

setup_travis
setup_docker
run_test
