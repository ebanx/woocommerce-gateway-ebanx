#!/usr/bin/env bash

rm -rf vendor
mv _vendor vendor
bash $TRAVIS_BUILD_DIR/.scripts/revoke_permissions.sh
