#!/usr/bin/env bash

set -e

project_dir(){
  export PROJECT_DIR=$(pwd)
  if [ "$TRAVIS_BUILD_DIR" != "" ]; then
    export PROJECT_DIR=$TRAVIS_BUILD_DIR
  fi
  echo PROJECT_DIR $PROJECT_DIR
}

project_dir

docker-compose -f travis/docker-compose.yml up -d

docker start woocommerce-gateway-ebanx-wp

docker exec woocommerce-gateway-ebanx-wp /bin/sh -c "/var/www/html/wp-content/plugins/woocommerce-gateway-ebanx/bin/setup.sh"