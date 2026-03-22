.PHONY: phpstan
phpstan:
	./vendor/bin/phpstan analyse --memory-limit=512M

.PHONY: phpstan-fix
phpstan-fix:
	./vendor/bin/phpstan analyse --memory-limit=512M --generate-baseline

.PHONY: test
test:
	./vendor/bin/phpunit --testdox