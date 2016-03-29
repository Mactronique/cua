check:
	docker-compose run --rm tool bash -ci 'php5dismod xdebug && composer self-update && ./cua check'
