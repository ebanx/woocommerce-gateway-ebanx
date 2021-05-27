#!/bin/bash

[[ $TRAVIS_COMMIT_MESSAGE =~ ^(\[tests skip\]) ]] && echo "TESTS SKIP" && exit 0;

setup_test() {
	echo setup_test
	cd $TRAVIS_BUILD_DIR/tests
	npm ci
}

run_tests() {
	EXIT_CODE=0
	echo run_tests
	setup_test
	cd $TRAVIS_BUILD_DIR/tests
	for i in ./woocommerce/cypress/integration/admin/*.js
		do
				npx cypress run --config video=false --project ./woocommerce -s "$i"
				CODE=$?
				echo EXIT_CODE TO $i: $CODE
				if [ "$CODE" != 0 ]; then
						EXIT_CODE=$CODE
				fi
		done
		exit $EXIT_CODE
}

setup_docker() {
	echo setup_docker
	sudo service mysql stop
	cd $TRAVIS_BUILD_DIR
        echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
	docker-compose up -d
}

setup_docker

while ! curl -s http://localhost > /dev/null; do echo waiting for woocommerce-container; sleep 10; done; run_tests
