#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

setup_test() {
  cd $TRAVIS_BUILD_DIR/tests
  sudo npm install

  sudo service mysql stop
  "export DISPLAY=:99.0"
  "sh -e /etc/init.d/xvfb start"
}

setup_docker() {
  cd $TRAVIS_BUILD_DIR
  docker-compose up -d --build
  sleep 20 # REMOVE THAT
}

setup_test
setup_docker

cd $TRAVIS_BUILD_DIR/tests
sudo node ./node_modules/.bin/cypress run --config videoRecording=false --project ./woocommerce -s cypress/integration/$TEST_COUNTRY.js
