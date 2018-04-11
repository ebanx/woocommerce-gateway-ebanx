#!/usr/bin/env bash

mv vendor _vendor
composer install --no-dev
composer dump-autoload -o
