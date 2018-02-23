#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

PLATFORM=$(echo $DORAEMON_TEST | cut -f1 -d-)
COUNTRY=$(echo $DORAEMON_TEST | cut -f2 -d-)

node ./node_modules/.bin/cypress run --config videoRecording=false --project ./$PLATFORM -s cypress/integration/$COUNTRY.js
