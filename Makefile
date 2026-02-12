PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public public/index.php

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src

install:
	composer install

validate:
	composer validate

update:
	composer dump-autoload