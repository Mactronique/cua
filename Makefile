##
## This file is part of Composer Update Analyser package.
##
## @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
## @copyright 2016-2019 - Jean-Baptiste Nahan
## @license MIT
##

.PHONY: check security console run-tests

check: vendor
	docker-compose run --rm tool bash -ci 'phpdismod xdebug && php composer.phar self-update && ./cua check $(c)'

security: vendor
	docker-compose run --rm tool bash -ci 'phpdismod xdebug && ./cua security $(c)'

console:
	docker-compose run --rm tool bash

run-tests:
	docker-compose run --rm tool bash -ci 'vendor/bin/atoum'

vendor: composer.lock
	docker-compose run --rm tool bash -ci 'phpdismod xdebug && php composer.phar self-update && php composer.phar install -o --prefer-dist'

composer.lock: composer.json composer.phar
	docker-compose run --rm tool bash -ci 'phpdismod xdebug && php composer.phar self-update && php composer.phar update -o --prefer-dist'

composer.phar:
	$(eval EXPECTED_SIGNATURE = "$(shell wget -q -O - https://composer.github.io/installer.sig)")
	$(eval ACTUAL_SIGNATURE = "$(shell php -r "copy('https://getcomposer.org/installer', 'composer-setup.php'); echo hash_file('SHA384', 'composer-setup.php');")")
	@if [ "$(EXPECTED_SIGNATURE)" != "$(ACTUAL_SIGNATURE)" ]; then echo "Invalid signature"; exit 1; fi
	php composer-setup.php
	rm composer-setup.php
