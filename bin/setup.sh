#!/bin/bash

set -e

function install_packages() {
    apt-get update
    apt-get upgrade

    echo INSTALL UTILS

    apt-get install -qq -y wget
    apt-get install -qq -y bzip2
    apt-get install -qq -y xvfb
    apt-get install -qq -y git
    apt-get install -y zlib1g-dev
    docker-php-ext-install zip

    echo INSTALL JAVA
    apt-get install -qq -y default-jre
    apt-get install -qq -y default-jdk

    echo INSTALL GTK
    apt-get install -qq -y libgtk-3-dev

    echo INSTALL FIREFOX

    cd /usr/local
    rm -rf firefox
    rm -rf /usr/bin/firefox
    wget http://ftp.mozilla.org/pub/firefox/releases/46.0/linux-x86_64/en-US/firefox-46.0.tar.bz2
    tar xvjf firefox-46.0.tar.bz2
    ln -s /usr/local/firefox/firefox /usr/bin/firefox
    firefox -v
}

n=0
until [ $n -ge 5 ]
do
  install_packages && break
  n=$[$n+1]
  sleep 1
done

bash /var/www/html/wp-content/plugins/woocommerce-gateway-ebanx/bin/run_tests.sh