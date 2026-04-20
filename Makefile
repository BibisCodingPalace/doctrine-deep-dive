DOCKER_COMPOSE ?= docker compose
EXEC_APP       = $(DOCKER_COMPOSE) exec app
PHP            = $(EXEC_APP) php
CONSOLE        = $(PHP) bin/console
COMPOSER       = $(EXEC_APP) composer

default: help

.PHONY: help
help: # Show help for this make file
	@grep -E '(^[a-zA-Z0-9 -]+.*#|^#########+(.*))'  Makefile | while read -r l; do printf "\033[1;32m$$(echo $$l: | sed -e 's/#########.*//g' | cut -f 1 -d':')\033[00m -$$(echo $$l | cut -f 2- -d'#')\n"; done

build: ## Build the application image
	$(DOCKER_COMPOSE) build

up: ## Start the stack in the background
	$(DOCKER_COMPOSE) up -d

down: ## Stop and remove the stack
	$(DOCKER_COMPOSE) down --remove-orphans

restart: down up ## Restart the stack

logs: ## Follow container logs
	$(DOCKER_COMPOSE) logs -f

shell: ## Open a shell inside the app container
	$(EXEC_APP) bash

install: ## Install PHP dependencies inside the container
	$(COMPOSER) install

migrate: ## Run Doctrine migrations
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --allow-no-migration

fixtures: ## Load Doctrine fixtures (purges DB)
	$(CONSOLE) doctrine:fixtures:load --no-interaction

cleanup-tests: ## Remove test database
	$(CONSOLE) doctrine:database:drop --env=test --if-exists --force

prepare-tests: ## Create test database
	$(CONSOLE) doctrine:database:create --env=test --if-not-exists
	$(CONSOLE) doctrine:migrations:migrate --env=test --no-interaction --allow-no-migration

run-all-tests: ## Run full PHPUnit Test Suite
	$(PHP) bin/phpunit --testdox

run-repository-tests: prepare-tests ## PHPUnit: repository integration tests (kernel + DB)
	$(PHP) bin/phpunit --testdox tests/Repository

run-web-tests: prepare-tests ## PHPUnit: small WebTestCase tests (HTTP + DB)
	$(PHP) bin/phpunit --testdox tests/Controller/TaskListRepositoryWebTest.php

run-stateless-tests: ## Run only tests, which do not require a database reset
	$(PHP) bin/phpunit --testdox --exclude-group stateful

test: cleanup-tests prepare-tests run-all-tests

compile-assets: ## Compile the asset map for prod
	$(CONSOLE) asset-map:compile --env=prod
