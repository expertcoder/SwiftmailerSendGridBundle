#!/bin/sh
# Helper script for testing purpose (local tests)
# One argument needed: Symfony major version to test against (ie. 2, 3 or 4)

if [ $# -eq 0 ] # check if argument exists
   then
	echo "Please add Symfony major version to test against (ex: 'sh tests/manual-test.sh 2' to test Sf 2.7)"
	exit 0;
fi

composer require --dev --no-update symfony/phpunit-bridge:~4.0 dunglas/symfony-lock:^$1
composer update --prefer-dist --no-interaction --prefer-stable --quiet
composer update --prefer-stable --prefer-lowest --prefer-dist --no-interaction
./vendor/bin/simple-phpunit install

composer validate --strict --no-check-lock
./vendor/bin/simple-phpunit