check:
	docker-compose run --rm tool bash -ci 'composer self-update && ./cua check'
