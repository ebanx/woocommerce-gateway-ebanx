#!/usr/bin/env bash

# we need this script to fail if any command fails
# when a command fails the variable `err` will turn 1
err=0
trap 'err=1' ERR

# ensure that files do not exist
# -f makes sure it returns 0 if the files didn't exist
rm -f $TRAVIS_BUILD_DIR/diff.txt $TRAVIS_BUILD_DIR/phpcs.json

# get diff from last travis build
# if PR, get diff from the base branch
commit_range=$TRAVIS_COMMIT_RANGE
echo "git diff $commit_range > $TRAVIS_BUILD_DIR/diff.txt"
git diff $commit_range > $TRAVIS_BUILD_DIR/diff.txt

# get all the style errors
# `|| true` makes sure it returns 0 even when phpcs fails
./vendor/bin/phpcs ./ --report=json --report-file=$TRAVIS_BUILD_DIR/phpcs.json || true

# makes sure no added line has error
./vendor/bin/diffFilter --phpcs $TRAVIS_BUILD_DIR/diff.txt $TRAVIS_BUILD_DIR/phpcs.json

# remove files created only for this scripts
# -f makes sure it returns 0 if the files didn't exist
rm -f $TRAVIS_BUILD_DIR/diff.txt $TRAVIS_BUILD_DIR/phpcs.json

# 0 if no command failed
# 1 if a command failed
exit ${err}
