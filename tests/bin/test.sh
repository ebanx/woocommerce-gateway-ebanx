#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

cd $TRAVIS_BUILD_DIR

docker-compose up -d
sleep 120 # REMOVE THAT

cd $TRAVIS_BUILD_DIR/tests

npm install
node ./node_modules/.bin/cypress run --config videoRecording=false --project ./woocommerce -s cypress/integration/$TEST_COUNTRY.js
