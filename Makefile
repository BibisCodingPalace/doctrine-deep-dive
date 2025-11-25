.PHONY: cleanup-tests prepare-tests run-all-tests run-stateless-tests compile-assets

cleanup-tests: ## Remove test database
	php bin/console doctrine:database:drop --env=test --if-exists --force

prepare-tests: ## Create test database
	php bin/console doctrine:database:create --env=test --if-not-exists
	php bin/console doctrine:migrations:migrate --env=test --no-interaction --allow-no-migration

run-all-tests: ## Run full PHPUnit Test Suite
	php bin/phpunit --testdox

run-stateless-tests: ## Run only tests, which do not require a database reset
	php bin/phpunit --testdox --exclude-group stateful

test: cleanup-tests prepare-tests run-all-tests

compile-assets:
	php bin/console asset-map:compile --env=prod
