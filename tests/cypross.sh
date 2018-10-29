#!/bin/bash

for i in ./woocommerce/cypress/integration/admin/*.js
    do
        ./node_modules/.bin/cypress run --config videoRecording=false --project ./woocommerce -s "$i"
        CODE=$?
        echo EXIT_CODE TO $i: $CODE
        if [ "$CODE" != 0 ]; then
            EXIT_CODE=$CODE
        fi
    done
