#!/usr/bin/env bash

php $(pwd)/vendor/squizlabs/php_codesniffer/bin/phpcs --extensions=php,phtml -v -n --parallel=5 .

# # we need this script to fail if any command fails
# # when a command fails the variable `err` will turn 1
# err=0
# trap 'err=1' ERR

# # get all the style errors
# # `|| true` makes sure it returns 0 even when phpcs fails
# ./vendor/bin/phpcs ./ || true

# # 0 if no command failed
# # 1 if a command failed
# exit ${err}
