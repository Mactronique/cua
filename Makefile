.PHONY: check

check: vendor
	docker-compose run --rm tool bash -ci 'phpdismod xdebug && composer self-update && ./cua check $(c)'

run-tests:
	docker-compose run --rm tool bash -ci 'vendor/bin/atoum'

vendor: composer.lock
	docker-compose run --rm tool bash -ci 'phpdismod xdebug && composer self-update && composer install -o --prefer-dist'

composer.lock: composer.json
	docker-compose run --rm tool bash -ci 'phpdismod xdebug && composer self-update && composer update -o --prefer-dist'
